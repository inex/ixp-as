package main

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"io/ioutil"
	"log"
	"net/http"

	_ "github.com/go-sql-driver/mysql"
)

// DB Config parameters
var dbuser = "atlasdev"
var dbpass = "atlas"
var dbhost = "37.139.9.87"
var dbname = "atlas_dev"

// URL
var jsonURL = "https://my.ams-ix.net/api/v1/members.json"

// Global variables
var ixpID int64
var networkID int64
var lanIDv6 int64
var lanIDv4 int64
var db *sql.DB
var sqlStr string
var sqlstring string

func main() {

	// Setup the DB parameters
	sqlstring = fmt.Sprintf("%s:%s@tcp(%s:3306)/%s", dbuser, dbpass, dbhost, dbname)

	// Connect to MySQL DB
	connectToDatabase()

	// Fetch the JSON data from URL
	res, _ := http.Get(jsonURL)
	body, _ := ioutil.ReadAll(res.Body)
	defer res.Body.Close()

	// Define some structs so that we can use them
	jsonMessage := jsonData{}
	members := MemberList{}
	ixps := IXPList{}
	connectionList := ConnectionList{}
	vlanList := VlanList{}
	vlanListSubV4 := VlanListSub{}
	vlanListSubV6 := VlanListSub{}
	vlanStruct := VLAN{}
	vlanStructIPvFoo := VLANIPvFoo{}
	var lastV6addr string

	// Unmarshal the outer JSON return
	err := json.Unmarshal(body, &jsonMessage)
	if err != nil {
		log.Printf("Error: %s", err)
	}

	// First insert the IXP data
	for _, jsons := range jsonMessage.IxpList {
		err = json.Unmarshal(jsons, &ixps)
		sqlStr := fmt.Sprintf("INSERT INTO ixps SET name='%s', shortname='%s', country='%s'", ixps.Name, ixps.Shortname, ixps.Country)
		dbExec, err := db.Exec(sqlStr)
		if err != nil {
			log.Printf("IXP Already Exists!")
			return
		}
		ixpID, _ = dbExec.LastInsertId()

		if err != nil {
			log.Printf("SQL Insert Error in IXP Insert: %s", err)
		}
		// Insert the VLAN data for each IXP
		for _, jsonsVlan := range ixps.VLAN {
			err = json.Unmarshal(jsonsVlan, &vlanStruct)
			err = json.Unmarshal(vlanStruct.IPV4, &vlanStructIPvFoo)
			if err != nil {
				log.Printf("Error: %s", err)
			}
			log.Printf("Inserting IXP: %s, %s", ixps.Name, ixps.Shortname)
			// Handle IPv4 network
			sqlStr = fmt.Sprintf("INSERT INTO lans SET ixp_id=%d, name='%s', protocol=%d, subnet='%s', masklen=%d", ixpID, vlanStruct.Name, 4, vlanStructIPvFoo.Prefix, vlanStructIPvFoo.MaskLength)
			dbExec, err = db.Exec(sqlStr)
			lanIDv4, _ = dbExec.LastInsertId()
			if err != nil {
				log.Printf("SQL Error: %s", err)
			}
			err = json.Unmarshal(vlanStruct.IPV6, &vlanStructIPvFoo)
			if err != nil {
				log.Printf("Error: %s", err)
			}
			log.Printf("VLANID: %d, Name: %s", vlanStruct.ID, vlanStruct.Name)
			// Handle IPv6 network
			sqlStr = fmt.Sprintf("INSERT INTO lans SET ixp_id=%d, name='%s', protocol=%d, subnet='%s', masklen=%d", ixpID, vlanStruct.Name, 6, vlanStructIPvFoo.Prefix, vlanStructIPvFoo.MaskLength)
			dbExec, err = db.Exec(sqlStr)
			lanIDv6, _ = dbExec.LastInsertId()

			if err != nil {
				log.Printf("SQL Error: %s", err)
			}
		}
	}
	// Next, insert the IXP Member data
	for _, jsons := range jsonMessage.MemberList {
		err = json.Unmarshal(jsons, &members)
		if err != nil {
			log.Printf("Error2: %s", err)
		}

		// Unmarshal down into connections and VLANs list
		err = json.Unmarshal(members.ConnectionList[0], &connectionList)
		err = json.Unmarshal(connectionList.VLANList[0], &vlanList)
		err = json.Unmarshal(vlanList.IPV6, &vlanListSubV6)

		log.Printf("V6ADDR: %s", vlanListSubV6.Address)
		// Insert all networks attached to the IXP
		// First check to make sure the v6 address is not stale (bad code alert!)
		if vlanListSubV6.Address != lastV6addr {
			sqlStr = fmt.Sprintf("INSERT INTO networks SET ixp_id=%d, v4asn='%d', v6asn='%d', name='%s'", ixpID, members.Asnum, members.Asnum, members.Name)

		} else {
			sqlStr = fmt.Sprintf("INSERT INTO networks SET ixp_id=%d, v4asn='%d', name='%s'", ixpID, members.Asnum, members.Name)
		}
		dbExec, err := db.Exec(sqlStr)
		if err != nil {
			log.Printf("SQL Insert Error: %s IXPID=%d", err, ixpID)
		}
		networkID, _ = dbExec.LastInsertId()
		if err != nil {
			log.Printf("SQL Insert Error: %s", err)
		}
		// Pull addresses from vlans list and insert into addresses table
		for _, vlanList2 := range connectionList.VLANList {
			err = json.Unmarshal(vlanList2, &vlanList)
			err = json.Unmarshal(vlanList.IPV4, &vlanListSubV4)
			log.Printf("IPv4: %s", vlanListSubV4.Address)
			// Handle IPv4 network
			sqlStr := fmt.Sprintf("INSERT INTO addresses SET lan_id=%d, network_id=%d, protocol=4, address='%s'", lanIDv4, networkID, vlanListSubV4.Address)
			_, err := db.Exec(sqlStr)
			if err != nil {
				log.Printf("SQL Insert Error: %s", err)
			}
			// IPv6 addresses
			log.Printf("VLAN JSON: %v", vlanListSubV6)

			err = json.Unmarshal(vlanList.IPV6, &vlanListSubV6)
			// Check to make sure we aren't recycling an old v6 address (more bad code!)
			if vlanListSubV6.Address == lastV6addr {
				vlanListSubV6.Address = ""
			}
			log.Printf("IPv6: %s", vlanListSubV6.Address)
			// Handle IPv6 network
			sqlStr = fmt.Sprintf("INSERT INTO addresses SET lan_id=%d, network_id=%d, protocol=6, address='%s'", lanIDv6, networkID, vlanListSubV6.Address)
			_, err = db.Exec(sqlStr)
			if err != nil {
				log.Printf("SQL Insert Error: %s", err)
			}
		}
		lastV6addr = vlanListSubV6.Address
		if err != nil {
			log.Printf("Error: %s", err)
		}
	}
}

// Function to connect to MySQL DB. Sets up the "db" handler
func connectToDatabase() {
	var err error
	db, err = sql.Open("mysql", sqlstring)
	if err != nil {
		log.Printf("Error connecting to database:" + err.Error())
	}
	err = db.Ping()
	if err != nil {
		log.Fatalf("Error connecting to database: %s", err.Error())
	}

	log.Printf("> Connected to MySQL Server <")

	if err != nil {
		log.Printf("Error preparing database statement: " + err.Error())
	}
}

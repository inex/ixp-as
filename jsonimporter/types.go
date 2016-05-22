package main

import "encoding/json"

// jsonData is the full json struct
type jsonData struct {
	Version    string            `json:"version"`
	Timestamp  string            `json:"timestamp"`
	IxpList    []json.RawMessage `json:"ixp_list"`
	MemberList []json.RawMessage `json:"member_list"`
}

// MemberList lists member peers of each IXP
type MemberList struct {
	Asnum            int               `json:"asnum"`
	Name             string            `json:"name"`
	URL              string            `json:"url"`
	ContactEmail     []string          `json:"contact_email"`
	ContactPhone     []string          `json:"contact_phone"`
	ContactHours     string            `json:"contact_hours"`
	PeeringPolicy    string            `json:"peering_policy"`
	PeeringPolicyURL string            `json:"peering_policy_url"`
	MemberSince      string            `json:"member_since"`
	Type             string            `json:"type"`
	ConnectionList   []json.RawMessage `json:"connection_list"`
}

// ConnectionList lists all connections to the IXP
type ConnectionList struct {
	IXPID    int               `json:"ixp_id"`
	State    string            `json:"state"`
	IFList   []json.RawMessage `json:"if_list"`
	VLANList []json.RawMessage `json:"vlan_list"`
}

// VlanList lists all vlans on a given connection
type VlanList struct {
	MACAddress string          `json:"mac_address"`
	VLANID     int             `json:"vlan_id"`
	IPV4       json.RawMessage `json:"ipv4"`
	IPV6       json.RawMessage `json:"ipv6"`
}

// VlanListSub lists details on connected vlans
type VlanListSub struct {
	Address     string `json:"address"`
	RouteServer bool   `json:"routeserver"`
	MaxPrefix   int    `json:"max_prefix"`
	ASMacro     string `json:"as_macro"`
}

// IXPList lists details of the IXP being queried
type IXPList struct {
	Name      string            `json:"name"`
	URL       string            `json:"url"`
	Shortname string            `json:"shortname"`
	Country   string            `json:"country"`
	IXFID     int               `json:"ixf_id"`
	IXPID     int               `json:"ixp_id"`
	VLAN      []json.RawMessage `json:"vlan"`
	Switch    json.RawMessage   `json:"switch"`
}

// VLAN is a VLAN list on each IXP
type VLAN struct {
	ID   int             `json:"id"`
	Name string          `json:"name"`
	IPV4 json.RawMessage `json:"ipv4"`
	IPV6 json.RawMessage `json:"ipv6"`
}

// VLANIPvFoo contains the prefix and mask of each associated subnet (v4/v6)
type VLANIPvFoo struct {
	Prefix     string `json:"prefix"`
	MaskLength int    `json:"mask_length"`
}

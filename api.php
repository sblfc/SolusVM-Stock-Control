<?php
/**
 * PHP Library for SolusVM's XMLRPC API
 *
 *  https://documentation.solusvm.com/display/DOCS/API
 *
 * @author     Benton Snyder
 * @website    http://www.bensnyde.me
 * @created    12/22/2012
 * @updated    4/2/2015
 */
class Solus {
    private $url;
    private $id;
    private $key;
    /**
     * Public constructor
     *
     * @access         public
     * @param          str, str, str
     * @return
     */
    function __construct($url, $id, $key) {
        $this->url = $url;
        $this->id = $id;
        $this->key = $key;
    }
    /**
     * Executes xmlrpc api call with given parameters
     *
     * @access       private
     * @param        array
     * @return       str
     */
    private function execute(array $params) {
        $params["id"] = $this->id;
        $params["key"] = $this->key;
        $params["rdtype"] = "json";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url . "/command.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:"));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $response = curl_exec($ch);
        curl_close($ch);
        if($response === false)
            throw new Exception("Curl error: " . curl_error($ch));
        return $response;
	}
    public function listPlans($type) {
        if(!in_array($type, array("xen hvm", "kvm", "xen", "openvz")))
            throw new Exception("Invalid Type");
        return $this->execute(array("action"=>"listplans", "type"=>$type));
    }
    /**
     * Retrives list of nodes annotated by their ID
     *
     *  https://documentation.solusvm.com/display/DOCS/List+Nodes+by+ID
     *
     * @access       public
     * @param        str
     * @return       str
     */
    public function listNodesByID($type) {
        if(!in_array($type, array("xen hvm", "kvm", "xen", "openvz")))
            throw new Exception("Invalid Type");
        return $this->execute(array("action"=>"node-idlist", "type"=>$type));
    }
    /**
     * Retrives list of nodes annotated by their name
     *
     *  https://documentation.solusvm.com/display/DOCS/List+Nodes+by+Name
     *
     * @access       public
     * @param        str
     * @return       str
     */
    public function listNodesByName($type) {
        if(!in_array($type, array("xen hvm", "kvm", "xen", "openvz")))
            throw new Exception("Invalid Type");
        return $this->execute(array("action"=>"listnodes", "type"=>$type));
    }
    /**
     * Retrieves list of IP address associated with specified node
     *
     *  https://documentation.solusvm.com/display/DOCS/List+All+IP+Addresses+for+a+Node
     *
     * @access       public
     * @param        int, int
     * @return       str
     */
    public function getNodeIPs($nodeid) {
        if(!is_numeric($nodeid))
            throw new Exception("Invalid NodeID");
        return $this->execute(array("action"=>"node-iplist", "nodeid"=>$nodeid));
    }
    /**
     * Retrieves list of node groups
     *
     *  https://documentation.solusvm.com/display/DOCS/List+Node+Groups
     *
     * @access       public
     * @param        int, str
     * @return       str
     */
    public function listNodeGroups($type) {
        if(!in_array($type, array("xen hvm", "kvm")))
            throw new Exception("Invalid Type");
        return $this->execute(array("action"=>"listnodegroups", "type"=>$type));
    }
	public function nodeStats($nodeid){
		if(!is_numeric($nodeid))
            throw new Exception("Invalid NodeID");
        return $this->execute(array("action"=>"node-statistics", "nodeid"=>$nodeid));
	}
}

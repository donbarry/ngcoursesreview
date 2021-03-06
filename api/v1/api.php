<?php
/** Class: API 
 *
 * This class encapsulates an API call
 * @author Uche Barry Ajokubi
 * @author Aayush Agrawal
 * @version 1.04 Nov 29, 2014.
 */
class API
{
    /**
     * Property: method
     * The HTTP method this request was made in, either GET, POST, PUT or DELETE
     */
    protected $method = '';
    /**
     * Property: endpoint
     * The Model requested in the URI. eg: /files
     */
    protected $endpoint = '';
    /**
     * Property: verb
     * An optional additional descriptor about the endpoint, used for things that can
     * not be handled by the basic methods. eg: /files/process
     */
    protected $verb = '';
    /**
     * Property: args
     * Any additional URI components after the endpoint and verb have been removed, in our
     * case, an integer ID for the resource. eg: /<endpoint>/<verb>/<arg0>/<arg1>
     * or /<endpoint>/<arg0>
     */
    protected $args = Array();
    /**
     * Property: file
     * Stores the input of the PUT request
     */
     protected $file = Null;

    /**
     * Constructor: __construct
     * @param string $request This is the encapsulation of the POST or GET request.
     * Allow for CORS, assemble and pre-process the data.
     */
    public function __construct($request) {
             header('Access-Control-Allow-Origin: *');
             header("Access-Control-Allow-Credentials: true"); 
             header('Access-Control-Allow-Headers: X-Requested-With');
             header('Access-Control-Allow-Headers: Content-Type,X-Requested-With,accept,Origin,Access-Control-Request-Method,Access-Control-Request-Headers,Authorization');
             header('Access-Control-Allow-Methods: POST, GET, DELETE, OPTIONS, PUT'); // http://stackoverflow.com/a/7605119/578667
             header('Access-Control-Max-Age: 86400'); 
        $this->args = explode('/', rtrim($request, '/'));
        $this->endpoint = array_shift($this->args);
        if (array_key_exists(0, $this->args) && !is_numeric($this->args[0])) {
            $this->verb = array_shift($this->args);
        }

        $this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $this->method = 'PUT';
            } else {
                throw new Exception("Unexpected Header");
            }
        }

        switch($this->method) {
        case 'DELETE':
        case 'POST':
            //$this->request=$this->_cleanInputs($_POST);
            $this->request = $this->_cleanInputs(file_get_contents("php://input"));
            break;
        case 'GET':
            $this->request = $this->_cleanInputs($_GET);
            break;
        case 'PUT':
            $this->request = $this->_cleanInputs($_GET);
            $this->file = file_get_contents("php://input");
            break;
        default:
            $this->_response('Invalid Method', 405);
            break;
        }

    }
    /**
     * Method: processAPI
     * Main entry point for $API class after constructor. Calls appropriate endpoint method and returns the response based on request parameters.
     * @return API response is returned.
     */
    public function processAPI() {
        if ((int)method_exists($this, $this->endpoint) > 0) {
            return $this->_response($this->{$this->endpoint}($this->args));
        }
        return $this->_response("No Endpoint: $this->endpoint", 404);
    }

    /**
     * Method: _response.
     * Sets the status header for a response (Default is "OK" - 200) then encodes $data to JSON for final response.
     * @param string $data is the returned data from the endpoint.
     * @param string $status is the HTTP status code for the response.
     * @return json encoded response.
     */
    private function _response($data, $status = 200) {
        header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
        return json_encode($data);
    }
    /**
     * Method: _cleanInputs.
     * Check for HTML tags in the request input and strips them using strip_tags.
     * @param string $data The input data from a request.
     * @return string $clean_input stripped request input.
     */
    private function _cleanInputs($data) {
        $clean_input = Array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = $this->_cleanInputs($v);
            }
        } else {
            $clean_input = trim(strip_tags($data));
        }
        return $clean_input;
    }
    /**
     * Method: _requestStatus.
     * Convert a status code to its description.
     * @param string $data The input data from a request.
     * @return string status description string.
     */
    private function _requestStatus($code) {
        $status = array(  
            200 => 'OK',
            404 => 'Not Found',   
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        ); 
        return ($status[$code])?$status[$code]:$status[500]; 
    }    
    /**
      * Method: logs.
      * This endpoint handles calls to /logs/list by retrieving the logs of a particular api key or all logs.
      * @param no parameters. like all endpoints, the POST arguments are in the $request property of $API class.
      * @return array of logs.
      */
  protected function logs(){
    // Connect to database
    $m = new MongoClient("mongodb://barry:barry@ds053310.mongolab.com:53310/coursereviews");
    //echo "Connection to database successfully<br/>";
    
    $db = $m->coursereviews;
    //echo "Database mydb selected<br/>";
    
    $collection = $db->logs;
    //echo "Collection selected successfully<br/>";
    if ($this->verb=="list"){
        $arr=[];
        if (isset($this->request)){
            $apiKey=json_decode($this->request);
            $apiKey=$apiKey->apiKey;
            $cursor = $collection->find(array('apiKey' => $apiKey));
        }
        else{
            $cursor = $collection->find(array('apiKey' => $apiKey));
        }
        // iterate cursor to display title of documents
        foreach ($cursor as $document) {
            $arr[]=$document;  //$cursor->getNext();
        }
        return $arr ; // json_encode($arr);
    }
       
}
    /**
      * Method: reviews.
      * This endpoint handles calls to 
      * /reviews/list by retrieving the reviews of a particular courseID
      * /reviews/add by adding the POSTed review object.
      * /reviews/delete by deleting the POSTed review object.
      * @param no parameters. like all endpoints, the POST arguments are in the $request property of $API class.
      * @return array of reviews if verb=list.
      * @return Insert message if verb=add.
      */
  protected function reviews(){
    // Connect to test database
    $m = new MongoClient("mongodb://barry:barry@ds053310.mongolab.com:53310/coursereviews");
    //echo "Connection to database successfully<br/>";
    
    $db = $m->coursereviews;
    //echo "Database mydb selected<br/>";
    
    $collection = $db->coursereviews;
    //echo "Collection selected successfully<br/>";
    if ($this->verb=="list"){
        $arr=[];
        $coursetofind=json_decode($this->request);
        $coursetofind=$coursetofind->courseID;
        $cursor = $collection->find(array('courseID' => $coursetofind));
        // iterate cursor to display title of documents
        foreach ($cursor as $document) {
            $arr[]=$document;
        }
        return $arr ; // json_encode($arr);
    }
    if ($this->verb=="add"){
        $request = json_decode($this->request);
        $uid = $request->uid;
        $email=$request->email;
        $courseID = $request->courseID;
        $reviewTitle = $request->reviewTitle;
        $bookTitle = $request->bookTitle;
        $reviewBody = $request->reviewBody;
        $userLocation=$request->userLocation;
        
            if ($this->validKeyPresent($request,$m)){
                //go ahead
                $apiKey=$request->apiKey;
            }else{
                return "Access Denied. You have no access to this functionality.";
            }
        
        $db = $m->coursereviews;
        //echo "Database coursereview selected";
        
        $collection = $db->coursereviews;
        //echo "Collection selected succsessfully";
        $document = array( 
            "uid" => $uid, 
            "email" => $email,
            "courseID" => $courseID,
            "reviewTitle" => $reviewTitle,
            "bookTitle" => $bookTitle,
            "reviewBody" => $reviewBody,
            "userLocation"=> $userLocation
        );
        $collection->insert($document);
        $this->log($apiKey,"Inserted",$document,$m);
        return "Inserted.";
    }
        if ($this->verb=="delete"){
            $request = json_decode($this->request);
            $reviewtodelete=$request->reviewID;
            if ($this->validKeyPresent($request,$m)){
                //go ahead
                $apiKey=$request->apiKey;
            }else{
                return "Access Denied. You have no access to this functionality.";
            }
            //echo "Connection to database successfully";
            $db = $m->coursereviews;
            //echo "Database coursereview selected";
            $collection = $db->coursereviews;
            //echo "Collection selected succsessfully";
            $cursor = $collection->remove(array('reviewID' => $reviewtodelete));
            $this->log($apiKey,"Deleted",$reviewtodelete);

            return "Deleted.";
        }
       
}
    /**
      * Method: courses
      * This endpoint handles calls to 
      * /courses/list by retrieving the list of courses
      * /courses/add by adding the POSTed course object 
      * /courses/update by updating the POSTed course ID's coursename 
      * /courses/delete by deleting the POSTed course ID.
      * @param no parameters. like all endpoints, the POST arguments are in the $request property of $API class.
      * @return array of courses if verb=list.
      * @return Insert message if verb=add.
      * @return Update message if verb=update.
      * @return Delete message if verb=delete.
      */
  protected function courses(){
    // Connect to test database
        $m = new MongoClient("mongodb://barry:barry@ds053310.mongolab.com:53310/coursereviews");
        //echo "Connection to database successfully<br/>";
        
        $db = $m->coursereviews;
        //echo "Database coursereviews selected<br/>";
        
        $collection = $db->courses;
        //echo "Collection selected successfully<br/>";
        if ($this->verb=="list"){
            $arr=[];
            //$coursetofind=json_decode($this->request);
            //$coursetofind=$coursetofind->courseID;
            $cursor = $collection->find();
            foreach ($cursor as $document) {
                $arr[]=$document;  //$cursor->getNext();
            }
            return $arr ; // json_encode($arr);
        }
        if ($this->verb=="add"){
            $request = json_decode($this->request);
            $courseName=$request->courseName;
            $courseID = $request->courseID;
            $bookTitle = $request->bookTitle;
            $bookAuthor = $request->bookAuthor;
            if ($this->validKeyPresent($request,$m)){
                //go ahead
                $apiKey=$request->apiKey;
            }else{
                return "Access Denied. You have no access to this functionality.";
            }
            
            $db = $m->coursereviews;
            //echo "Database coursereview selected";
            
            $collection = $db->courses;
            //echo "Collection selected succsessfully";
            $document = array( 
                "courseID" => $courseID, 
                "courseName" => $courseName,
                "bookTitle" => $bookTitle,
                "bookAuthor" => $bookAuthor
            );
            $collection->insert($document);
            $this->log($apiKey,"Inserted",$document,$m);
            return "Inserted.";
        }
        if ($this->verb=="delete"){
            $request = json_decode($this->request);
            $coursetodelete=$request->courseID;
            if ($this->validKeyPresent($request,$m)){
                //go ahead
                $apiKey=$request->apiKey;
            }else{
                return "Access Denied. You have no access to this functionality.";
            }
            //echo "Connection to database successfully";
            $db = $m->coursereviews;
            //echo "Database coursereview selected";
            $collection = $db->courses;
            //echo "Collection selected succsessfully";
            $cursor = $collection->remove(array('courseID' => $coursetodelete));
            $this->log($apiKey,"Deleted",$coursetodelete);

            return "Deleted.";
        }
        if ($this->verb=="update"){
            $request = json_decode($this->request);
            $courseToUpdate=$request->courseID;
            $newCourseName=$request->newCourseName;
            if ($this->validKeyPresent($request,$m)){
                //go ahead
                $apiKey=$request->apiKey;
            }else{
                return "Access Denied. You have no access to this functionality.";
            }
            //echo "Connection to database successfully";
            $db = $m->coursereviews;
            //echo "Database coursereview selected";
            $collection = $db->courses;
            //echo "Collection selected succsessfully";
            $retval = $collection->findAndModify(
                array('courseID' => $courseToUpdate)
                ,array('$set'=>array('courseName'=>$newCourseName)),
                null,
                null);
            $this->log($apiKey,"Updated",$courseToUpdate . " to " . $newCourseName ,$m);
            return "Updated.";
        }


    }    
    /**
      * Method: validKeyPresent.
      * Check validity of an api Key.
      * @param Object $request is the POST request for the method to extract the api key from it (if it exists).
      * @param Object $mongo is the database object created by the caller.
      * @return bool true if api key is found or if api key is inexistent/invalid.
      */    
    private function validKeyPresent($request,$mongo){
            if (isset($request->apiKey)){
                $apiKey=$request->apiKey;
                $db = $mongo->coursereviews;
                //echo "Database coursereview selected";
                $collection = $db->keys;
                $apiFound = $collection->findOne(array('apiKey' => $apiKey));
                if ($apiFound){
                    return true;
                }
            }
            return false;
    }
    /**
      * Method: log
      * Log an event to mongo database.
      * @param string $apiKey is the apiKey being used for the action.
      * @param string $action is the activity/method being logged such as "update"/"insert"...
      * @param Object $parameters is the object being used. for example the course or review being added.
      * @param Object $mongo is the database object created by the caller.
      * @return bool true if api key is found or if api key is inexistent/invalid.
      */    
    private function log($apiKey,$action,$parameters,$mongo){
                $db = $mongo->coursereviews;
                //echo "Database coursereview selected";
                $collection = $db->logs;
                //echo "Collection selected succsessfully";
                $document = array( 
                    "apiKey" => $apiKey, 
                    "action" => $action,
                    "parameters" => $parameters,
                    "date" => date("Y-m-d H:i:s")
                );
                $collection->insert($document);
                return true;
    }
}

// Requests from the same server don't have a HTTP_ORIGIN header
if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

try {
    $req=$_REQUEST['request'];
    $origin=$_SERVER['HTTP_ORIGIN'];
    //$API = new API($_REQUEST['request'], $_SERVER['HTTP_ORIGIN']);
    $API = new API($req,$origin);
    echo $API->processAPI();
    
} catch (Exception $e) {
    echo $req;
    echo json_encode(Array('error' => $e->getMessage()));
}
?>
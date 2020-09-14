<?php
//____________________________________________________________
//**************************INIT******************************
//____________________________________________________________
declare(strict_types=1);
//Init session
SESSION_START();

//Set time zone
date_default_timezone_set("Europe/Brussels");

//Accesing the cookies
$acumulatedOrder = isset($_COOKIE['acumulatedOrder']) ? floatval($_COOKIE['acumulatedOrder']) : 0;
//____________________________________________________________
//***********************VARIABLES****************************
//____________________________________________________________
//Getting info from the SESSION
$addressStreet = (!empty($_SESSION["s_addressStreet"])) ? $_SESSION["s_addressStreet"] : "";
$streetNumber = (!empty($_SESSION["s_streetNumber"])) ? $_SESSION["s_streetNumber"] : "";
$city = (!empty($_SESSION["s_city"])) ? $_SESSION["s_city"]:"";
$zipCode = (!empty($_SESSION["s_zipCode"])) ? $_SESSION["s_zipCode"] : "";

$email = "";
$selectedProducts = [];
$userMessage = "";
$totalOrder = "";
 
//Gettind the options to order food=1 : order food,  food=0: order drinks
$orderType = (isset($_GET["food"])) ? $_GET["food"] : "1";

//Selecting which products are displayed
//Drinks or Foods
if ($orderType == '1') {

    $products = [
      ['name' => 'Club Ham', 'price' => 3.20],
      ['name' => 'Club Cheese', 'price' => 3],
      ['name' => 'Club Cheese & Ham', 'price' => 4],
      ['name' => 'Club Chicken', 'price' => 4],
      ['name' => 'Club Salmon', 'price' => 5] 
    ];

}else{  

    $products = [
    ['name' => 'Cola', 'price' => 2],
    ['name' => 'Fanta', 'price' => 2],
    ['name' => 'Sprite', 'price' => 2],
    ['name' => 'Ice-tea', 'price' => 3],
    ];

}
//____________________________________________________________
//***********************FUNCTIONS****************************
//____________________________________________________________

//-----------------------------------------------------------------------------
// NAME: cleanInput
// FUNCTION: clean the data typed
//-----------------------------------------------------------------------------
function cleanInput ($data) {

    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;

};

//-----------------------------------------------------------------------------
// NAME: validateEmail
// FUNCTION:Validate the Email field typed by the user
//-----------------------------------------------------------------------------

function validateEmail($email){
      $errMessage = "";
      //Validating e-mail 
      if (empty($_POST["email"])) {
        $errMessage=$errMessage."e-mail is empty !";

    }else{

         $email = cleanInput ($_POST["email"]);
         if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

             $errMessage=$errMessage."Invalidate email !";

         } 
    }

 return errMessage;
}

//-----------------------------------------------------------------------------
// NAME: validateData
// FUNCTION:Validate the data Entry
//-----------------------------------------------------------------------------
function validateData () {
    
    global $userMessage,$email,$addressStreet,$streetNumber,$city,$zipCode,$selectedProducts,$products; 
    // TODO: fix dirty code       

    $errMessage = "";
    $email = $_POST["email"];
    validateEmail($errMessage)

    
 
    //validate address-street
    if (empty($_POST["street"])){
         $errMessage=$errMessage.", Street name is empty !";
    }else{
     $addressStreet=cleanInput($_POST["street"]); 
     if (!preg_match("/^[a-zA-Z-' ]*$/",$addressStreet)) {
        $errMessage=$errMessage.", Street name Only letters and white space allowed !";        
      }
    }
 
    //validate address-street-number
    if (empty($_POST["streetnumber"])){
         $errMessage=$errMessage.", Street Number is empty !";
    }else{
     $streetNumber=cleanInput($_POST["streetnumber"]); 
     if (!filter_var($streetNumber,FILTER_VALIDATE_INT) ){
         $errMessage=$errMessage.", Street number Only number allowed !"; 
     }
    }
 
    //validate address-city
    if (empty($_POST["city"])){
         $errMessage=$errMessage.", City is empty";
    }else{
     $city=cleanInput($_POST["city"]); 
    }
 
    //validate address-Zip Code
    if (empty($_POST["zipcode"])){
         $errMessage=$errMessage.", ZipCode is empty !";
    }else{
     $zipCode=cleanInput($_POST["zipcode"]); 
     if(!filter_var($zipCode,FILTER_VALIDATE_INT)){
      $errMessage=$errMessage.", Zipcode Only number allowed !"; 
     }
    }    
    
   //Validate product Selection
   if (empty($_POST["products"])){
    $errMessage=$errMessage.", At least one product has to be selected (Order empty) :( !"; 
   }else{
     foreach ($_POST["products"] as $index => $productOrdered) {            
      $selectedProducts[$products[$index]['name']] =$productOrdered;   
     }   
    
   }

   if (!empty($errMessage)){
     $errMessage="Please check and fix this issues :) : ".$errMessage;
     $errMessage= " <div class='alert alert-dismissible alert-danger'>     
     <h4 class='alert-heading'>Warning!</h4> 
     <p class='mb-0'>$errMessage
     </p> </div>";

     $userMessage=$errMessage;
    return false; 
   } else{
     return true;
   }
 }
 


 //-----------------------------------------------------------------------------
 // NAME: calculateDeliveryTime
// FUNCTION: Calculate the delivery Time
//-----------------------------------------------------------------------------
function calculateDeliveryTime($delivery){  
  
  $hourDelivery=new DateTime();     
  
  if($delivery=="normal"){
    $hourDelivery->modify('+2 hours');  
  }
  else{
    $hourDelivery->modify('+45 minute');
  }  
  
  $hourDelivery = $hourDelivery->format('Y-m-d H:i:s');
  
  return " <div class='alert alert-dismissible alert-info'>     
  <h4 class='alert-heading'>Congratulations - Order Recived !</h4> 
  <p class='mb-0'> Your order will be delivered at:<strong> $hourDelivery </strong>
  </p> </div>";
}

//-----------------------------------------------------------------------------
// NAME: totalOrder
// FUNCTION: sumarize the amount of order
//-----------------------------------------------------------------------------
function totalOrder($selectedProducts,$products){
  $totalOrder=0.0;
  foreach ($selectedProducts as $key => $selected) {
    foreach ($products as $index => $producto) {
      if ($producto['name']==$key){
        $totalOrder=$totalOrder+$producto['price'];
      }
    }  
    
  }
  return $totalOrder;
}

//-----------------------------------------------------------------------------
// NAME: sendEmail
// FUNCTION: send the email, using the input that the user typed
//-----------------------------------------------------------------------------
function sendEmail($email){
    
  
     $headers .= "From: VictorOrtega". "\r\n";    
     $message="Dear Consumer: \n\n Su orden esta lista!.\n\n\nSincerely : The Staff";
     $subject="Order Recived";
     mail($email,$subject,$message,$headers);
}
 

// Main execution

if ($_SERVER["REQUEST_METHOD"]=="POST"){    
  if(validateData()){    
      //Saving in session variables
      $_SESSION["s_addressStreet"]=$addressStreet;
      $_SESSION["s_streetNumber"]=$streetNumber;
      $_SESSION["s_city"]=$city;
      $_SESSION["s_zipCode"]=$zipCode;
      
      //Calculating the delivery time
      $userMessage=calculateDeliveryTime($_POST["delivery"]);     

      //Calculating the total of the order
      $totalOrder=totalOrder($selectedProducts,$products);
      
      if ($totalOrder > 0){
         $acumulatedOrder +=$totalOrder;
         setcookie('acumulatedOrder',strval($acumulatedOrder),time() + (60*60*24*90),'/','becode.io');
         $totalOrder=number_format($totalOrder,2);
         $totalOrder="<h4> Total Order <strong>&euro; $totalOrder </strong></h4>";
      }
      //Sending the email
       sendEmail($email);      
    };
    
};

require 'orderForm.php';
<?php
function getPizza($pizzaType, $Customer)
{
    $Customer = ucfirst($Customer);
    $Address = getAddress($Customer);
    $Price = getPrice($pizzaType);

    echo 'Creating new order...<br>';
    echo "A {$pizzaType} pizza should be sent to {$Customer}.<br>";
    echo "The address: {$Address}.<br>";
    echo "The bill is €{$Price}.<br>";
    echo "Order finished.<br><br>";
}

function getAddress($Customer)
{
    switch ($Customer) {
        case 'Koen':
            return 'Somewhere in Antwerpen';
        case 'Manuele':
            return 'somewhere in Belgium';
        case 'Students':
            return 'BeCode office';
        default:
            return 'N/A';
    }
}

function getPrice($pizzaType)
{
    switch ($pizzaType) {
        case 'marguerita':
            return '5';
        case 'golden':
            return '100';
        case 'calzone':
            return '10';
        case 'hawaii':
            throw new Exception('No way!!');
        default:
            return 'Comming Soon!!';
    }
}

function main() {
    getPizza('calzone', 'koen');
    getPizza('marguerita', 'manuele');
    getPizza('golden', 'students');
};

main();
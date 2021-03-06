<?php

namespace FerencBalogh\Szamlazzhu;

use FerencBalogh\Szamlazzhu\Receipt\ReceiptCreate;
use FerencBalogh\Szamlazzhu\Receipt\ReceiptDelete;
use FerencBalogh\Szamlazzhu\Exceptions\InvalidUserException;
use FerencBalogh\Szamlazzhu\Exceptions\InvalidCreateReceiptException;
use FerencBalogh\Szamlazzhu\Exceptions\InvalidDeleteReceiptException;

class Szamlazz
{
    /**
     * Szamlazz constructor.
     */
    public function __construct()
    {
        $this->checkConnection();
    }

    /**
     * Create receipt
     */
    public function createReceipt($elotag, $fizmod, $rendelesszam, $brutto, $email, $targy, $uzenet)
    {
        $receipt = new ReceiptCreate($elotag, $fizmod, $rendelesszam, $brutto, $email, $targy, $uzenet);
        return $receipt->createReceipt();
    }
    /**
     * Delete receipt
     */
    public function deleteReceipt($nyugtaszam, $email, $targy, $uzenet)
    {
        $receipt = new ReceiptDelete($nyugtaszam, $email, $targy, $uzenet);
        return $receipt->deleteReceipt();
    }
    /**
     * Check Username & Password
     */
    protected function checkConnection()
    {
        if (config('szamlazz.username') === null || config('szamlazz.password') === null) {
            throw new InvalidUserException('Missing username and password. Setup .env variables please.');
        }
    }
}
<?php

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;

class BasicContext extends RawMinkContext implements Context, SnippetAcceptingContext
{
    protected $session;

    protected $request;

    protected $client;

    public function __construct()
    {
    }

}

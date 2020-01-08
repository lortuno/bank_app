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

    /**
     * @When I click :linkName
     */
    public function iClick($linkName)
    {
        $this->getPage()->clickLink($linkName);
    }

    /**
     * @Then the :tag value must be :value
     * @param $tag
     * @param $value
     * @throws Exception
     */
    public function checkValueMatches($tag, $value)
    {
        $page = $this->getMink()->getSession()->getPage();
        $currentValue = $page->find('css', $tag)->getAttribute('value');

        if ($currentValue !== $value) {
            throw new Exception('Incorrect value');
        }
    }


}

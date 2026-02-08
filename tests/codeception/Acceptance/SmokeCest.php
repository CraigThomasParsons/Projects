<?php

final class SmokeCest
{
    public function loadsHomepage(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        $I->see('Laravel');
    }
}

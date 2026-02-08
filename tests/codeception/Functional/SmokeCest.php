<?php

final class SmokeCest
{
    public function loadsHomepage(FunctionalTester $I): void
    {
        $I->amOnPage('/');
        $I->see('Laravel');
    }
}

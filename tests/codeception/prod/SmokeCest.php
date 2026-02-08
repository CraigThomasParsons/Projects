<?php

declare(strict_types=1);

final class SmokeCest
{
    public function homepageLoadsSuccessfully(ProdTester $I): void
    {
        $I->amOnPage('/');
        $I->seeResponseCodeIs(200);
        $I->see('Projects');
    }
}

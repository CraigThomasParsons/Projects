<?php

declare(strict_types=1);

final class SmokeCest
{
    public function loadsHomepage(FunctionalTester $I): void
    {
        $I->amOnPage('/');
        $I->see('Projects');
    }
}

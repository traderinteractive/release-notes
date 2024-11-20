<?php

namespace Guywithnose\ReleaseNotes\Tests\Change;

use Guywithnose\ReleaseNotes\Change\Change;
use Guywithnose\ReleaseNotes\Change\ChangeFactory;
use Guywithnose\ReleaseNotes\Change\ChangeList;
use Guywithnose\ReleaseNotes\Change\ChangeListFactory;
use Guywithnose\ReleaseNotes\Type\TypeManager;
use PHPUnit\Framework\TestCase;

class ChangeListTest extends TestCase
{
    public function testLargestChange()
    {
        $typeManager = TypeManager::getSemanticTypeManager();
        $changeFactory = new ChangeFactory($typeManager);
        $minorChange = new Change('test change 1', $typeManager->getMinorType());
        $majorChange = new Change('test change 2', $typeManager->getMajorType());
        $changeList = new ChangeList(
            $typeManager,
            [
                $minorChange,
                $majorChange,
            ]
        );
        $largestChangeType = $changeList->largestChange();
        $this->assertEquals($majorChange->getType(), $largestChangeType);
    }
}

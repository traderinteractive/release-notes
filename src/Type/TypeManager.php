<?php

namespace Guywithnose\ReleaseNotes\Type;

final class TypeManager
{
    /**
     * @type array list of useable types
     */
    private $_types;

    /**
     * @type Type the lowest type is Backward compatible breaking
     */
    private $_bcType;

    /**
     * @type Type the lowest type is a major change
     */
    private $_majorType;

    /**
     * @type Type the lowest type is a minor change
     */
    private $_minorType;

    /**
     * @type Type the default type to use
     */
    private $_defaultType;

    public function __construct()
    {
        $this->_types = [];
        $this->_bcType = null;
        $this->_majorType = null;
        $this->_minorType = null;
        $this->_defaultType = null;
    }

    public function setBCType(Type $type)
    {
        $this->_bcType = $type;
    }

    public function getBCType()
    {
        return $this->_bcType;
    }

    public function setMajorType(Type $type)
    {
        $this->_majorType = $type;
    }

    public function getMajorType()
    {
        return $this->_majorType;
    }

    public function setMinorType(Type $type)
    {
        $this->_minorType = $type;
    }

    public function getMinorType()
    {
        return $this->_minorType;
    }

    public function setDefaultType(Type $type)
    {
        $this->_defaultType = $type;
    }

    public function getDefaultType()
    {
        return $this->_defaultType;
    }

    public function add(Type $type)
    {
        if ($this->getTypeByCode($type->getCode()) !== null) {
            throw new TypeCodeExistsException('Type with code ' . $type->getCode() . 'already exists.');
        }

        $this->_types[] = $type;

        usort($this->_types, [Type::class, 'rcmp']);
    }

    /**
     * @return Type|null the type if found or null otherwise
     */
    public function getTypeByCode(string $code)
    {
        foreach ($this->_types as $type) {
            if ($type->getCode() === $code) {
                return $type;
            }
        }
    }

    /**
     * @return Type|null the type if found or null otherwise
     */
    public function getTypeByName(string $name)
    {
        foreach ($this->_types as $type) {
            if ($type->getName() === $name) {
                return $type;
            }
        }
    }

    /**
     * Returns an array of key values pairs for use in menu selection
     *
     * @return array
     */
    public function getTypesForCommand(): array
    {
        $data = [];
        foreach ($this->_types as $type) {
            $data[$type->getCode()] = $type->getDescription();
        }

        return $data;
    }

    public static function getSemanticTypeManager()
    {
        $manager = new TypeManager();

        $manager->add(new Type('Backward Compatible Breakers', 'B', 'Backward Compatibility Breakers', 100));
        $manager->add(new Type('Major', 'M', 'Major Features', 80));
        $manager->add(new Type('Minor', 'm', 'Minor Features', 60));
        $manager->add(new Type('Bug', 'b', 'Bug Fixes', 40));
        $manager->add(new Type('Developer', 'd', 'Developer Changes', 20));
        $manager->add(new Type('Ignore', 'x', 'Remove Commit from Release Notes', 0));

        $manager->setBCType($manager->getTypeByCode('B'));
        $manager->setMajorType($manager->getTypeByCode('M'));
        $manager->setMinorType($manager->getTypeByCode('m'));
        $manager->setDefaultType($manager->getTypeByCode('m'));

        return $manager;
    }

    public static function getJiraTypeManager()
    {
        $manager = new TypeManager();

        $manager->add(new Type('Backward Compatible Breakers', 'B', 'Backward Compatibility Breakers', 100));
        $manager->add(new Type('Epic', 'e', 'Epic Issues', 90));
        $manager->add(new Type('Idea', 'i', 'Ideas', 85));
        $manager->add(new Type('Initiative', 'n', 'Initiatives', 80));
        $manager->add(new Type('New Feature', 'f', 'New Features', 80));
        $manager->add(new Type('Rock', 'r', 'Rocks', 80));
        $manager->add(new Type('Service Request', 'q', 'Service Requests', 80));
        $manager->add(new Type('Story', 's', 'Story Enhancements', 70));
        $manager->add(new Type('Task', 'k', 'Tasks', 60));
        $manager->add(new Type('Sub-task', 't', 'Sub-tasks', 56));
        $manager->add(new Type('Subtask', 'T', 'Subtasks', 55));
        $manager->add(new Type('Bug', 'b', 'Bug fixes', 40));
        $manager->add(new Type('Defect', 'd', 'Defects', 30));
        $manager->add(new Type('Maintenance', 'm', 'Maintenance changes', 25));
        $manager->add(new Type('Monitoring', 'o', 'Monitoring', 20));
        $manager->add(new Type('Spike', 'S', 'Spike for Research', 10));
        $manager->add(new Type('Ignore', 'x', 'Remove Commit from Release Notes', 0));
        $manager->add(new Type('Unknown', 'u', 'Unknown type/No type selected', -10));

        $manager->setBCType($manager->getTypeByCode('B'));
        $manager->setMajorType($manager->getTypeByCode('T'));
        $manager->setMinorType($manager->getTypeByCode('S'));
        $manager->setDefaultType($manager->getTypeByCode('u'));

        return $manager;
    }
}

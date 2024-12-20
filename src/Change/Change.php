<?php

namespace Guywithnose\ReleaseNotes\Change;

use Guywithnose\ReleaseNotes\Type\Type;

class Change implements ChangeInterface
{
    public const TYPE_IGNORE = 'x';

    /** @type Type The type of the change. */
    protected $_type;

    /** @type string A message describing the change. */
    protected $_message;

    /** @type string A link to the change. */
    protected $_link = '';

    /**
     * Initialize the change.
     *
     * @param string $message The pull request message.
     * @param Type $type The pull request type.
     */
    public function __construct($message, Type $type)
    {
        $this->_message = $message;
        $this->setType($type);
    }

    /**
     * Sets the type.
     *
     * @param Type $type The type
     *
     * @return void
     */
    public function setType(Type $type)
    {
        $this->_type = $type;
    }

    /**
     * Get the type.
     *
     * @return Type The type code.
     */
    public function getType(): Type
    {
        return $this->_type;
    }

    /**
     * Sets the link.
     *
     * @param string $link The link
     *
     * @return void
     */
    public function setLink(string $link)
    {
        $this->_link = $link;
    }

    /**
     * Get the link.
     *
     * @return string The link.
     */
    public function getLink(): string
    {
        return $this->_link;
    }

    /**
     * Get the displayable type.
     *
     * @return string The displayable type.
     */
    public function displayType()
    {
        return $this->_type ? $this->_type->getDescription() : '';
    }

    /**
     * Returns a short markdown snippet of the change for use in release notes.
     *
     * @return string A short representation of the change.
     */
    public function displayShort(): string
    {
        if (!empty($this->getLink())) {
            return '* [' . strtok($this->_message, "\n") . "]({$this->getLink()})";
        }

        return '* ' . strtok($this->_message, "\n");
    }

    /**
     * Returns a long markdown version of the change for use in user display.
     *
     * @return string A long representation of the change.
     */
    public function displayFull(): string
    {
        return $this->_message;
    }
}

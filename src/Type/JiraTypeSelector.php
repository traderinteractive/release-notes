<?php

namespace Guywithnose\ReleaseNotes\Type;

use JiraRestApi\Issue\IssueService;
use JiraRestApi\JiraException;
use Guywithnose\ReleaseNotes\Change\ChangeInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class JiraTypeSelector
{
    /**
     * @type TypeManager type manager with the specified types
     */
    private $_typeManager;

    /**
     * @type IssueService issue service object
     */
    private $_issueService;

    /**
     * @type string regular expression pattern to search for
     */
    private $_pattern;

    /**
     * Initialize the change.
     *
     * @param TypeManager  $typeManager  Type name or short description.
     * @param IssueService $issueService Single letter code used for choosing this type in a menu
     * @param string       $pattern      Longer description of type.
     * @param \Symfony\Component\Console\Output\OutputInterface $output The command output.
     */
    public function __construct(
        TypeManager $typeManager,
        IssueService $issueService,
        string $pattern,
        OutputInterface $output
    ) {
        $this->_typeManager = $typeManager;
        $this->_issueService = $issueService;
        $this->_pattern = $pattern;
        $this->_output = $output;
    }

    /**
     * @param ChangeInterface $change Change to check for type from Jira
     *
     * @return Type type of commit or default if unable to determine
     */
    public function getChangeType(ChangeInterface $change): Type
    {
        $text = $change->displayShort();
        $matches = [];
        if (preg_match_all($this->_pattern, $text, $matches)) {
            foreach ($matches[0] as $key) {
                try {
                    $issue = $this->_issueService->get($key);
                    $issuetype = $issue->fields->issuetype->name;
                    $type = $this->_typeManager->getTypeByName($issuetype);
                    if ($type === null && !empty($issuetype)) {
                        $newCode = $this->getUniqueCode();
                        if (!empty($newCode)) {
                            $type = new Type($issuetype, $newCode, $issuetype, 1);
                            $this->_typeManager->add($type);
                        }
                    }

                    if ($type !== null) {
                        $host = $this->_issueService->getConfiguration()->getJiraHost();
                        $change->setLink("{$host}/browse/{$key}");
                        return $type;
                    }
                } catch (JiraException $e) {
                    $this->_output->writeln("Could not find Jira issue {$key}", OutputInterface::VERBOSITY_DEBUG);
                }
            }
        }

        return $this->_typeManager->getDefaultType();
    }

    /**
     * Gets a unique code used for dynamically added types.
     *
     * @return string A unique unused code or an empty value if none are available.
     */
    private function getUniqueCode(): string
    {
        $possibleCodes = str_split('123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
        $existingCodes = array_keys($this->_typeManager->getTypesForCommand());

        $availableCodes = array_values(array_diff($possibleCodes, $existingCodes));

        if (!empty($availableCodes)) {
            return $availableCodes[0];
        }

        return '';
    }
}

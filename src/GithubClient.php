<?php

namespace Guywithnose\ReleaseNotes;

use Github\AuthMethod;
use Github\Client;
use Github\ResultPager;

class GithubClient
{
    /** @type Github\Client The repositories receiver. */
    protected $client;

    /** @type string */
    protected $owner;

    /** @type string */
    protected $repo;


    /**
     * Initialize the github client wrapper for the repository.
     *
     * @param \Github\Client The github client.
     * @param string $owner The owner name of the github repository.
     * @param string $repo The name of the github repository.
     */
    public function __construct(Client $client, $owner, $repo)
    {
        $this->client = $client;
        $this->owner = $owner;
        $this->repo = $repo;
    }

    /**
     * Create a github client wrapper with automated token-based authentication.
     *
     * @param string $token The API token to authenticate with.
     * @param string $owner The owner name of the github repository.
     * @param string $repo The name of the github repository.
     * @param string $apiUrl The base url to the github API if different from the main github site
     *                       (i.e., GitHub Enterprise).
     * @return self The github client wrapper, authenticated against the API.
     * @throws \Exception if the token is invalid
     */
    public static function createWithToken($token, $owner, $repo, $apiUrl = null)
    {
        $client = new Client();

        if ($apiUrl !== null) {
            $client->setEnterpriseUrl($apiUrl);
        }

        $client->authenticate($token, AuthMethod::ACCESS_TOKEN);

        // Verify that the token works
        $authenticatedUser = $client->currentUser()->show();
        if (isset($authenticatedUser['message']) && $authenticatedUser['message'] === 'Requires authentication') {
            throw new \Exception('Bad credentials');
        }

        return new static($client, $owner, $repo);
    }

    /**
     * Get the latest release's tag name for the repo.
     *
     * @param string $releaseBranch The branch to find releases on, or null to find tag from any branch.
     * @return string|null The release's tag name if one exists.
     */
    public function getLatestReleaseTagName($releaseBranch = null)
    {
        $releases = $this->client->api('repo')->releases()->all($this->owner, $this->repo);

        foreach ($releases as $release) {
            if ($releaseBranch === null || $release['target_commitish'] === $releaseBranch) {
                return $release['tag_name'];
            }
        }

        return null;
    }

    /**
     * Check if a tag already exists on a repo.
     *
     * @param string $tagName The tag name to check
     *
     * @return bool True if the tag exists
     */
    public function tagExists(string $tagName): bool
    {
        $tags = $this->client->api('repo')->tags($this->owner, $this->repo);

        foreach ($tags as $tag) {
            if ($tagName === $tag['name']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fetch the commits from github between the two commits/tags/branches/etc.
     *
     * @param string|null $startCommitish The beginning commit - excluded from results.
     *                                    If this is null, all ancestors of $endCommitish will be
     *                                    returned.
     * @param string $endCommitish The end commit - included in results.
     * @return array The list of changes in the commit range.
     */
    public function getCommitsInRange($startCommitish, $endCommitish)
    {
        if ($startCommitish !== null) {
            return $this->getCommitsSinceTag($startCommitish, $endCommitish);
        }

        return $this->getCommitsOnBranch($endCommitish);
    }

    /**
     * Fetches the commits to the repo since the given tag.
     *
     * @param string $tagName The old tag.
     * @param string $branch The branch to check
     * @return array The commits made to the repository since the old tag.
     */
    public function getCommitsSinceTag($tagName, $branch = 'masterBranch')
    {
        $commits = $this->client->api('repo')->commits()->compare($this->owner, $this->repo, $tagName, $branch);

        return $commits['commits'];
    }

    /**
     * Fetch the commits for the repo's branch.
     *
     * @param string $branch The branch to check
     * @return array The commits made to the repository's branch.
     */
    public function getCommitsOnBranch($branch = 'master')
    {
        $commits = $this->client->api('repo')->commits()->all($this->owner, $this->repo, array('sha' => $branch));
        return $commits;
    }

    /**
     * Submits the given release to github.
     *
     * @param array $release The release information.
     * @return string The release url
     */
    public function createRelease(array $release): string
    {
        $result = $this->client->api('repo')->releases()->create($this->owner, $this->repo, $release);

        return $result['html_url'];
    }
}

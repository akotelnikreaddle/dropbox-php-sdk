<?php

declare(strict_types=1);

namespace Kunnu\Dropbox\Models;

class SearchResult extends BaseModel
{

    /**
     * Indicates what type of match was found for the result
     *
     * @var string
     */
    protected $matchType;

    /**
     * File\Folder Metadata
     *
     * @var FileMetadata|FolderMetadata
     */
    protected $metadata;


    /**
     * Create a new SearchResult instance
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
        $matchType = $this->getDataProperty('match_type');
        $this->matchType = $matchType['.tag'] ?? null;
        $this->setMetadata();
    }

    /**
     * Set Metadata
     *
     * @return void
     */
    protected function setMetadata()
    {
        $metadata = $this->getDataProperty('metadata');

        if (is_array($metadata)) {
            $this->metadata = ModelFactory::make($metadata);
        }
    }

    /**
     * Indicates what type of match was found for the result
     *
     * @return bool
     */
    public function getMatchType()
    {
        return $this->matchType;
    }

    /**
     * Get the Search Result Metadata
     *
     * @return FileMetadata|FolderMetadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
}

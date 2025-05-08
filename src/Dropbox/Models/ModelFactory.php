<?php

declare(strict_types=1);

namespace Kunnu\Dropbox\Models;

class ModelFactory
{

    /**
     * Make a Model Factory
     *
     * @param  array $data Model Data
     *
     * @return ModelInterface
     */
    public static function make(array $data = []): FileMetadata|FolderMetadata|TemporaryLink|MetadataCollection|SearchResults|DeletedMetadata|BaseModel
    {
        if (static::isFileOrFolder($data)) {
            $tag = $data['.tag'];

            //File
            if (static::isFile($tag)) {
                return new FileMetadata($data);
            }

            //Folder
            if (static::isFolder($tag)) {
                return new FolderMetadata($data);
            }
        }

        //Temporary Link
        if (static::isTemporaryLink($data)) {
            return new TemporaryLink($data);
        }

        //List
        if (static::isList($data)) {
            return new MetadataCollection($data);
        }

        //Search Results
        if (static::isSearchResult($data)) {
            return new SearchResults($data);
        }

        //Deleted File/Folder
        if (static::isDeletedFileOrFolder($data)) {
            return new DeletedMetadata($data);
        }

        //Base Model
        return new BaseModel($data);
    }

    protected static function isFileOrFolder(array $data): bool
    {
        return isset($data['.tag']) && isset($data['id']);
    }

    /**
     * @param string $tag
     */
    protected static function isFile($tag): bool
    {
        return $tag === 'file';
    }

    /**
     * @param string $tag
     */
    protected static function isFolder($tag): bool
    {
        return $tag === 'folder';
    }

    protected static function isTemporaryLink(array $data): bool
    {
        return isset($data['metadata']) && isset($data['link']);
    }

    protected static function isList(array $data): bool
    {
        return isset($data['entries']);
    }

    protected static function isSearchResult(array $data): bool
    {
        return isset($data['matches']);
    }

    protected static function isDeletedFileOrFolder(array $data): bool
    {
        return !isset($data['.tag']) || !isset($data['id']);
    }
}

<?php

namespace TheIconic\Fixtures\Parser;

use TheIconic\Fixtures\Fixture\Fixture;
use TheIconic\Fixtures\Exception\InvalidParserException;

/**
 * Class XmlParser
 * @package TheIconic\Fixtures\Parser
 */
class XmlParser extends AbstractParser
{
    /**
     * XML extension
     *
     * @var string
     */
    protected static $parsableExtension = '.xml';

    /**
     * Parses and MySQL dump XML file.
     *
     * @param $source
     * @return Fixture
     * @throws \TheIconic\Fixtures\Exception\InvalidParserException
     */
    public function parse($source)
    {
        $fixtureArray = [];
        $z = new \XMLReader;
        $z->open($source);

        $doc = new \DOMDocument;

        while ($z->read() && $z->name !== 'table_data');
        $tableName = $z->getAttribute('name');

        $rowNum = 0;
        while ($z->read() && $z->name !== 'row');
        while ($z->name === 'row')
        {
            $node = simplexml_import_dom($doc->importNode($z->expand(), true));

            $totalAttributes = $node->count();
            for ($i = 0; $i < $totalAttributes; $i++) {
                foreach ($node->field[$i]->attributes() as $attribute) {
                    $attribute = (string) $attribute;
                    $value = (string) $node->field[$i];
                    $namespaces = $node->field[$i]->getNamespaces(true);

                    if (empty($namespaces)) {
                        $fixtureArray[$tableName][$rowNum][$attribute] = $value;
                    }
                }
            }

            $rowNum++;
            $z->next('row');
        }

        if (empty($fixtureArray)) {
            throw new InvalidParserException("It was not possible to parse the XML file: $source");
        }

        return Fixture::create($fixtureArray);
    }
}

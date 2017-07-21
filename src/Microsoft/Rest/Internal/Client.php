<?php
namespace Microsoft\Rest\Internal;

use Microsoft\Rest\ClientInterface;
use Microsoft\Rest\Internal\Data\DataAbstract;
use Microsoft\Rest\Internal\Data\MapData;
use Microsoft\Rest\Internal\Data\RootData;
use Microsoft\Rest\Internal\Types\TypeAbstract;
use Microsoft\Rest\OperationInterface;

final class Client implements ClientInterface
{
    /**
     * @param string $path see https://swagger.io/docs/specification/paths-and-operations/ for path templating.
     * @param string $httpMethod see https://swagger.io/specification/#pathItemObject for more details.
     * @param array $operationData see https://swagger.io/specification/#operationObject for more details.
     * @return OperationInterface
     */
    function createOperationFromData($path, $httpMethod, array $operationData)
    {
        return Operation::createFromOperationData(
            $this,
            RootData::create(
                $operationData,
                MapData::appendPathKey(MapData::appendPathKey('$paths', $path), $httpMethod)));
    }

    /**
     * @param DataAbstract $definitionsData
     * @return ClientInterface
     */
    static function createFromData(DataAbstract $definitionsData)
    {
        $client =new Client(TypeAbstract::createMapFromData(
            $definitionsData, '#/definitions/'));
        $client->removeRefTypesFromMap($client->typeMap);
        return $client;
    }

    /**
     * @param TypeAbstract[] $typeMap
     * @return TypeAbstract[]
     */
    function removeRefTypesFromMap(array $typeMap)
    {
        /**
         * @var TypeAbstract[]
         */
        $result = [];
        foreach ($typeMap as $name => $value) {
            $result[$name] = $value->removeRefTypes($this);
        }
        return $result;
    }

    /**
     * @param string $name
     * @return TypeAbstract|null
     */
    function getType($name)
    {
        return isset($this->typeMap[$name]) ? $this->typeMap[$name] : null;
    }

    /**
     * @var TypeAbstract[]
     */
    private $typeMap;

    /**
     * @param TypeAbstract[] $typeMap
     */
    private function __construct(array $typeMap)
    {
        $this->typeMap = $typeMap;
    }
}
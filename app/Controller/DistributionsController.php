<?php

namespace MHFSaveManager\Controller;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use MHFSaveManager\Database\EM;
use MHFSaveManager\Model\Distribution;
use MHFSaveManager\Model\DistributionItems;
use MHFSaveManager\Service\ItemsService;
use MHFSaveManager\Service\ResponseService;
use MHFSaveManager\Model\Character;

/**
 *
 */
class DistributionsController extends AbstractController
{
    protected static string $itemName = 'distribution';
    protected static string $itemClass = Distribution::class;
    protected static string $subItemClass = DistributionItems::class;
    
    public static function Index()
    {
        $distributions = EM::getInstance()->getRepository(self::$itemClass)->findAll();
        $characters = \MHFSaveManager\Database\EM::getInstance()->getRepository(Character::class)->findAll();
        
        
        include_once ROOT_DIR . '/app/Views/distributions.php';
    }
    
    public static function IndexTest()
    {
        $distributions = EM::getInstance()->getRepository(self::$itemClass)->findAll();
    
        /** @var Distribution $distribution */
        foreach ($distributions as $distribution) {
            echo '<hr>';
            printf('Name: %s <br>', $distribution->getEventName());
            printf('Desc: %s <br>', $distribution->getDescription());
            printf('Type: %s <br>', Distribution::$types[$distribution->getType()]);
            echo '<br><b>Items:</b><br>';
            $items = EM::getInstance()->getRepository(DistributionItems::class)->findBy(array('distribution_id' => $distribution->getId()));
            foreach ($items as $i=>$item) {
                $itemIdString = $item->getItemIdString();
                $itemName = array_key_exists($itemIdString, ItemsService::getForLocale()) 
                    ? ItemsService::getForLocale()[$itemIdString]['name'] : "Corrupted item";
                $itemType = array_key_exists($item->getItemType(), DistributionItems::$types) 
                    ? DistributionItems::$types[$item->getItemType()] : "Unknown type";
                printf('ItemNr: %s <br>Type: %s <br>Item: %s <br>Amount: %s<br><br>', 
                    $i+1, $itemType, $itemName, $item->getQuantity());
            }
        }
        echo "<hr>";
    }
    
    public static function EditDistribution()
    {
        $distribution = new Distribution();
    
        if (isset($_POST['id']) && $_POST['id'] > 0) {
            $distribution = EM::getInstance()->getRepository(self::$itemClass)->find($_POST['id']);
        } else {
            EM::getInstance()->persist($distribution);
        }
        
        $distribution->setType($_POST['type']);
        $distribution->setCharacterId((int)$_POST['characterId']);
        $distribution->setTimesAcceptable((int)$_POST['timesacceptable']);
        $distribution->setEventName($_POST['name']);
        $distribution->setDescription($_POST['desc']);
        $distribution->setDeadline($_POST['deadline'] ? new \DateTime($_POST['deadline']) : null);
        $distribution->setMinHr((int)$_POST['minhr']);
        $distribution->setMaxHr((int)$_POST['maxhr']);
        $distribution->setMinSr((int)$_POST['minsr']);
        $distribution->setMaxSr((int)$_POST['maxsr']);
        $distribution->setMinGr((int)$_POST['mingr']);
        $distribution->setMaxGr((int)$_POST['maxgr']);
        
        $items = array();
        $toRemove = EM::getInstance()->getRepository(self::$subItemClass)->findBy(['distribution_id' => $distribution->getId()]);
        foreach ($_POST['items'] as $postItem) {
            $item = new DistributionItems();
            if (isset($postItem['id']) && $postItem['id'] > 0) {
                $item = EM::getInstance()->getRepository(self::$subItemClass)->find($postItem['id']);
                unset($toRemove[$item->getId()]);
            } else {
                EM::getInstance()->persist($item);
            }
            $item->setItemType((int)$postItem['type']);
            $item->setItemIdString($postItem['itemId']);
            $item->setQuantity((int)$postItem['amount']);
            $item->setDistributionId($distribution->getId());
            array_push($items, $item);
        }
        foreach ($toRemove as $item) {
            EM::getInstance()->remove($item);
        }
        EM::getInstance()->flush();
    
        ResponseService::SendOk();
    }
    
    /**
     * @return void
     */
    public static function ExportDistributions(): void
    {
        $records = EM::getInstance()->getRepository(self::$itemClass)->findAll();
        self::arrayOfModelsToCSVDownload($records);
    }
    
    /**
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public static function ImportDistributions(): void
    {
        self::importFromCSV();
    }
}

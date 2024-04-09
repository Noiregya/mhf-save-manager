<?php

namespace MHFSaveManager\Controller;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use MHFSaveManager\Database\EM;
use MHFSaveManager\Model\ShopItem;
use MHFSaveManager\Service\EditorGeneratorService;
use MHFSaveManager\Service\ItemsService;
use MHFSaveManager\Service\UIService;

/**
 *
 */
class ShopController extends AbstractController
{
    protected static string $itemName = 'shop';
    protected static string $itemClass = ShopItem::class;
    /**
     * @return void
     */
    public static function Index(): void
    {
        $UILocale = UIService::getForLocale();
        
        $data = [];
        
        $shopItems = EM::getInstance()->getRepository(self::$itemClass)->matching(
            Criteria::create()->where(Criteria::expr()->gt('shop_type', '0'))
        )->toArray();
        
        $modalFieldInfo = [
            $UILocale['ID']                   => [
                'type'     => 'Hidden',
                'disabled' => true,
            ],
            $UILocale['Shop Type']             => [
                'type'    => 'Array',
                'options' => ShopItem::$shoptypes,
            ],
            $UILocale['Category']             => [
                'type'    => 'Array',
                'options' => ShopItem::$categories,
            ],
            $UILocale['Item']                 => [
                'type'    => 'Array',
                'options' => ItemsService::getForLocale(),
            ],
            $UILocale['Cost']                 => ['type' => 'Int', 'min' => 1, 'max' => 999, 'placeholder' => '1-999'],
            $UILocale['GRank Req']            => ['type' => 'Int', 'min' => 0, 'max' => 999, 'placeholder' => '0-999'],
            $UILocale['Trade Quantity']       => ['type' => 'Int', 'min' => 1, 'max' => 999, 'placeholder' => '1-999'],
            $UILocale['Maximum Quantity']     => ['type' => 'Int', 'min' => 0, 'max' => 999, 'placeholder' => '0-999'],
            $UILocale['Road Floors Req']      => ['type' => 'Int', 'min' => 0, 'max' => 999, 'placeholder' => '0-999'],
            $UILocale['Weekly Fatalis Kills'] => ['type' => 'Int', 'min' => 0, 'max' => 999, 'placeholder' => '0-999'],
        ];
    
        $fieldPositions = [
            'headline' => $UILocale['ID'],
            [
                
                $UILocale['Shop Type'],
                $UILocale['Category'],
                $UILocale['Item'],
            ],
            [
                $UILocale['Cost'],
                $UILocale['GRank Req'],
                $UILocale['Trade Quantity'],
                $UILocale['Maximum Quantity'],
                $UILocale['Road Floors Req'],
                $UILocale['Weekly Fatalis Kills'],
            ],
        ];
        
        foreach ($shopItems as $shopItem) {
            $itemId = self::numberConvertEndian($shopItem->getItemid(), 2);
            $itemData = ItemsService::getForLocale()[$itemId];
            $data[] = [
                $UILocale['ID']                   => $shopItem->getId(),

                $UILocale['Shop Type']             =>
                [
                    'id'   => $shopItem->getShoptype(),
                    'name' => $shopItem->getShoptypeFancy(),
                ],            

                $UILocale['Category']             =>
                    [
                        'id'   => $shopItem->getShopid(),
                        'name' => $shopItem->getShopidFancy(),
                    ],
                $UILocale['Item']                 =>
                    [
                        'id'   => $itemId,
                        'name' => $itemData['name'] ? : $UILocale['No Translation!'],
                    ],
                $UILocale['Cost']                 => $shopItem->getCost(),
                $UILocale['GRank Req']            => $shopItem->getMinGr(),
                $UILocale['Trade Quantity']       => $shopItem->getQuantity(),
                $UILocale['Maximum Quantity']     => $shopItem->getMaxQuantity(),
                $UILocale['Road Floors Req']      => $shopItem->getRoadFloors(),
                $UILocale['Weekly Fatalis Kills'] => $shopItem->getRoadFatalis(),
            ];
        }
        
        $actions = [
        ];
        
        echo EditorGeneratorService::generateDynamicTable('MHF Shop', static::$itemName, $modalFieldInfo, $fieldPositions, $data, $actions);
    }
    
    
    
    /**
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public static function EditShopItem(): void
    {
        self::SaveItem(static function ($item) {
            $item->setItemid(hexdec(self::numberConvertEndian(hexdec($_POST[self::localeWS('Item')]), 2)));
            $item->setMaxQuantity($_POST[self::localeWS('Maximum Quantity')]);
            $item->setQuantity($_POST[self::localeWS('Trade Quantity')]);
            $item->setMinGr($_POST[self::localeWS('GRank Req')]);
            $item->setCost($_POST[self::localeWS('Cost')]);
            $item->setShopid($_POST[self::localeWS('Category')]);
            $item->setRoadFloors($_POST[self::localeWS('Road Floors Req')]);
            $item->setRoadFatalis($_POST[self::localeWS('Weekly Fatalis Kills')]);
    
            $item->setShoptype($_POST[self::localeWS('Shop Type')]);
            $item->setMinHr(0);
            $item->setMinSr(0);
            $item->setStoreLevel(1);
        });
    }
    
    /**
     * @return void
     */
    public static function ExportShopItems(): void
    {
        
        $records = EM::getInstance()->getRepository(self::$itemClass)->matching(
            Criteria::create()->where(Criteria::expr()->eq('shop_type', '10')));
        
        self::arrayOfModelsToCSVDownload($records);
    }
    
    /**
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public static function ImportShopItems(): void
    {
        self::importFromCSV('n.shop_type = 10');
    }
}

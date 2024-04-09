<style>
    .carousel-indicators>li {
        background-color: #999;
    }

    .ammopouch-item {
        height: 79px;
        width: 117px;
        border: 1px solid #000;
    }

    .carousel-inner {
        min-height: 500px;
        min-width: 780px;
    }

    #slidetext {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
    }
</style>

<div id="itemboxSlotEdit" class="modal fade" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemboxSlotEditTitle"><?php echo $UILocale['Editing Itemslot'] ?>: <b></b></h5>
            </div>
            <div class="modal-body">
                <h6><?php echo $UILocale['Item'] ?>:</h6>
                <div class="input-group mb-2">
                    <select class="form-control" id="itemboxSlotItem">
                        <?php
                        foreach (\MHFSaveManager\Service\ItemsService::getForLocale() as $id => $item) {
                            printf('<option data-icon="%s" data-color="%s" value="%s">%s</option>', $item['icon'], $item['color'], $id, $item['name']);
                        }
                        ?>
                    </select>
                </div>

                <h6><?php echo $UILocale['Quantity'] ?>:</h6>
                <div class="input-group mb-2">
                    <input type="number" class="form-control" id="itemboxSlotQuantity" placeholder="999" min="1" max="999">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo $UILocale['Close'] ?></button>
                <button type="button" class="btn btn-primary" id="itemboxSlotSave"><?php echo $UILocale['Save'] ?></button>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <?php
    $slots = array_fill(0, 20, ['id' => null, 'quantity' => null, 'name' => 'Empty Slot', 'slot' => null, 'icon' => 'Dummy', 'color' => '']);
    // echo $ammopouch;
    foreach ($ammopouch as $item) {
        if ($item->getSlot() >= 0 && $item->getSlot() < 10) {
            $tmpItem = isset(\MHFSaveManager\Service\ItemsService::getForLocale()[$item->getId()])
                ? \MHFSaveManager\Service\ItemsService::getForLocale()[$item->getId()]
                : ['icon' => 'Dummy', 'color' => ''];

            $slots[$item->getSlot()] = [
                'id' => $item->getId(),
                'quantity' => $item->getQuantity(),
                'name' => $item->getName(),
                'slot' => $item->getSlot(),
                'icon' => $tmpItem['icon'],
                'color' => $tmpItem['color'],
            ];
        }
    }

    // Render slots in two columns
    for ($column = 0; $column < 1; $column++) {
        echo '<div class="col">';
        for ($slot = 0; $slot < 10; $slot++) {
            $item = $slots[$slot];
            echo '<div class="ammopouch-item" data-id="' . htmlspecialchars($item['id'] ?? '') . '" data-quantity="' . htmlspecialchars($item['quantity'] ?? '') . '" data-slot="' . htmlspecialchars($item['slot'] ?? '') . '">';
            echo '<img class="item-icon" src="/img/item/' . htmlspecialchars($item['icon'] ?? '') . htmlspecialchars($item['color'] ?? '') . '.png">';
            echo '<span style="font-size: 12px;"><b>[x' . htmlspecialchars($item['quantity'] ?? '') . ']</b><br>' . htmlspecialchars(implode(' ', preg_split('/(?=[A-Z])/', $item['name'] ?? '')), ENT_QUOTES, 'UTF-8') . '</span>';
            echo '</div>';
        }
        echo '</div>';
    }
    ?>
</div>
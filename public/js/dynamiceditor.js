$(document).ready(function () {
    const table = $('#${itemName}table').DataTable();

    $('#shopItemSelect').select2({
        theme: 'bootstrap4',
    });

    $('#shopCategorySelect').select2({
        theme: 'bootstrap4',
    });
    $('#createShopItem').click(function() {
        $('#shopItemTitle > b')[0].innerHTML = '';
        $('#shopItemSelect').val('0000');
        $('#shopItemSelect').trigger('change');
        $('#shopCategorySelect').val('0');
        $('#shopCategorySelect').trigger('change');
        $('#shopCost').val('');
        $('#shopGRank').val('');
        $('#shopTradeQuantity').val('');
        $('#shopMaximumQuantity').val('');
        $('#shopRoadFloors').val('');
        $('#shopFatalis').val('');
        $('#shopItemModal').modal('show');
    });

    $('#shoptable').on('click', '.editItem', function () {
        $('#shopItemTitle > b')[0].innerHTML = $(this).data('id');
        $('#shopItemSelect').val($(this).data('itemid'));
        $('#shopItemSelect').trigger('change');
        $('#shopCategorySelect').val($(this).data('categoryid'));
        $('#shopCategorySelect').trigger('change');
        $('#shopCost').val($(this).data('cost'));
        $('#shopGRank').val($(this).data('grank'));
        $('#shopTradeQuantity').val($(this).data('quantity'));
        $('#shopMaximumQuantity').val($(this).data('max_quantity'));
        $('#shopRoadFloors').val($(this).data('roadfloors'));
        $('#shopFatalis').val($(this).data('fatalis'));
        $('#shopItemModal').modal('show');
    });

    $('#shopSave').click(function() {
        let id = $('#shopItemTitle > b')[0].innerHTML;
        let item = $('#shopItemSelect').find(':selected');
        let category = $('#shopCategorySelect').find(':selected');
        let cost = $('#shopCost').val();
        let grank = $('#shopGRank').val();
        let tradeQuantity = $('#shopTradeQuantity').val();
        let maximumQuantity = $('#shopMaximumQuantity').val();
        let roadFloors = $('#shopRoadFloors').val();
        let fatalis = $('#shopFatalis').val();

        let saveButton = $(this);
        saveButton.prop('disabled', true);
        if (item.length === 0 || category.length === 0 || cost === '' || grank === '' || tradeQuantity === '' || maximumQuantity === '' || roadFloors === '' || fatalis === '') {
            alert('Please fill all fields with valid data!');
            saveButton.prop('disabled', false);
            return;
        }

        if (isNaN(id)) {
            id = '';
        }

        let data = {
            id: id,
            item: item.val(),
            category: category.val(),
            cost: cost,
            grank: grank,
            tradeQuantity: tradeQuantity,
            maximumQuantity: maximumQuantity,
            roadFloors: roadFloors,
            fatalis: fatalis,
        };

        $.ajax({
            url: '/servertools/shop/save',
            type: 'POST',
            data: data,
        }).then(function(response) {
            let button = $('.editItem[data-id="' + id + '"]');
            if (button.length > 0) {
                let cells = button.parents('tr').children('td');
                cells[1].innerHTML = category.text();
                cells[2].innerHTML = item.text();
                cells[3].innerHTML = cost;
                cells[4].innerHTML = grank;
                cells[5].innerHTML = tradeQuantity;
                cells[6].innerHTML = maximumQuantity;
                cells[7].innerHTML = roadFloors;
                cells[8].innerHTML = fatalis;
                saveButton.prop('disabled', false);
                table.row(button.parents('tr')).invalidate().draw(false);
            } else {
                location.reload()
                //table.row.add(['ID VOM RESPOONSE', category.text(), shop.text(), cost, grank, tradeQuantity, maximumQuantity, boughtQuantity, roadFloors, fatalis]).draw();
            }

            $('#shopItemModal').modal('hide');
        }).catch(function(response) {
            alert(response.message);
            saveButton.prop('disabled', false);
        });
    });

    $('#shoptable').on('click', '.deleteItem', function () {
        let formdata = new FormData();
        let itemId = $(this).attr('data-id');
        formdata.append('item', itemId);

        if (!window.confirm('Are you sure you want to delete the entry with the ID : ' + itemId)) {
            return;
        }

        let rowToRemove = $(this).parents('tr');

        $.ajax({
            url: '/servertools/shop/delete/' + itemId,
            type: 'POST',
            data: formdata,
            processData: false,
            contentType: false,
            success: function (result) {
                table.row(rowToRemove).remove().draw();
            },
            error: function (result) {
                alert(result.responseJSON.message);
            }
        });
    });

    $('#importShop').click(function() {
        if (!window.confirm('This will overwrite every Shop Item. Beware!')) {
            return;
        }

        $('#importShopInput').trigger('click');
    });

    $('#importShopInput').change(function() {
        let formdata = new FormData();
        if($(this).prop('files').length <= 0) {
            return;
        }

        let file =$(this).prop('files')[0];
        formdata.append('shopCSV', file);

        $.ajax({
            url: '/servertools/shop/import',
            type: 'POST',
            data: formdata,
            processData: false,
            contentType: false,
            error: function (result) {
                alert(result.responseJSON.message)
            },
            success: function () {
                location.reload();
            }
        });
    });
});

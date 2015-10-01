<div class="border" style="margin: 0 auto; color: #333; border: 5px solid #333; padding: 30px; color: #333; font-family: sans-serif; width: 600px; ">
    <table width="100%" style="color: #555;">
        <tbody>
            <tr>
                <td><img src="data:image/png;base64,<?php echo base64_encode(file_get_contents(WWW_ROOT . 'img/OpenEMIS_logistics.png')); ?>"></td>
                <td width="60%" style="font-size: 50px;"><?php echo _PACKAGE_LABEL_HEADING ?></td>
            </tr>
        </tbody>
    </table>
    <table width="100%" style="color: #555;">
        <tbody>
            <tr>
                <td>
                    <?php
                    $shipeFromLines = explode(_DELEM5, $shipFrom);
                    foreach ($shipeFromLines as $shipeFromLine) {
                        echo $shipeFromLine . '<br/>';
                    }
                    ?>
                </td>
                <td width="60%">
                    <strong><?php echo _PACKAGE_LABEL_SHIPMENT_DATE ?></strong><?php echo date('m/d/Y', strtotime($shipmentDate)) ?><br/>
                    <strong><?php echo _PACKAGE_LABEL_SHIPMENT_CODE ?></strong><?php echo $shipmentCode ?>
                </td>
            </tr>
        </tbody>
    </table>
    <table width="100%">
        <tbody>
            <tr><td>&nbsp;</td></tr>
        </tbody>
    </table>
    <table width="100%" style="color: #555;">
        <tbody>
            <tr>
                <td style="font-size: 20px; font-weight: bold;" width="20%" valign="top">Ship To : <br/></td>
                <td style="font-size: 20px;">
                    <?php
                    $shipToLines = explode(_DELEM5, $shipTo);
                    foreach ($shipToLines as $shipToLine) {
                        echo $shipToLine . '<br/>';
                    }
                    ?>
                </td>
            </tr>
        </tbody>
    </table>
    <table width="100%">
        <tbody>
            <tr><td>&nbsp;</td></tr>
        </tbody>
    </table>
    <table width="100%" cellpadding="5" border="1" style="border-collapse: collapse; text-align: center; color: #555; font-size:12px;">
        <thead>
            <tr>
                <th><?php echo _PACKAGE_LABEL_PACKAGE_CODE ?></th>
                <th><?php echo _PACKAGE_LABEL_PACKAGE_TYPE ?></th>
                <th><?php echo _PACKAGE_LABEL_ITEM_CODE ?></th>
                <th><?php echo _PACKAGE_LABEL_ITEM_NAME ?></th>
                <th><?php echo _PACKAGE_LABEL_ITEM_TYPE ?></th>
                <th><?php echo _PACKAGE_LABEL_QUANTITY ?></th>
                <th><?php echo _PACKAGE_LABEL_PACKAGE_WEIGHT ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($packageList as $row): ?>
                <tr>
                    <td><?php echo $row['packageCode'] ?></td>
                    <td><?php echo $row['packageCode'] ?></td>
                    <td><?php echo $row['itemCode'] ?></td>
                    <td><?php echo $row['itemName'] ?></td>
                    <td><?php echo $row['itemtype'] ?></td>
                    <td><?php echo $row['quantity'] ?></td>
                    <td><?php echo $row['packageWeight'] . ' ' . $weightUnit ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
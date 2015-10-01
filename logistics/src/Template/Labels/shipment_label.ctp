<div class="border" style="margin: 0 auto; border: 5px solid #333; padding: 10px; color: #333; font-family: sans-serif; width: 800px; height: 500px;">
    <table width="100%">
        <tbody>
            <tr>
                <td width="50%">
                    <table width="100%" cellpadding="5">
                        <tbody>
                            <tr>
                                <td><?php echo _SHIP_LABEL_SHIPMENT_CODE ?></td>
                                <td><?php echo $shipmentCode ?></td>
                            </tr>
                            <tr>
                                <td colspan="2"><img src="data:image/png;base64,<?php echo $barcode ?>"></td>
                            </tr>
                            <tr>
                                <td><?php echo _SHIP_LABEL_PACKAGE_CODE ?></td>
                                <td><?php echo $packageCode ?></td>
                            </tr>
                            <tr>
                                <td><?php echo _SHIP_LABEL_PACKAGE_COUNT ?></td>
                                <td><?php echo $packageCount ?></td>
                            </tr>
                            <tr>
                                <td><?php echo _SHIP_LABEL_PACKAGE_WEIGHT ?></td>
                                <td><?php echo $packageWeight ?></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td width="50%">
                    <table width="100%" cellpadding="5">
                        <tbody>
                            <tr>
                                <td>Ship From : </td>
                            </tr>
                            <tr>
                                <td>
                                    <?php 
                                    $shipeFromLines = explode(_DELEM5, $shipFrom);
                                    foreach($shipeFromLines as $shipeFromLine) {
                                        echo $shipeFromLine. '<br/><br/>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <table width="100%">
                        <tbody>
                            <tr>
                                <td style="font-size: 20px; font-weight: bold;">Ship To : <br/></td>
                            </tr>
                            <tr>
                                <td style="font-size: 30px; color: #555;">
                                    <?php 
                                    $shipToLines = explode(_DELEM5, $shipTo);
                                    foreach($shipToLines as $shipToLine) {
                                        echo $shipToLine . '<br/>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</div>
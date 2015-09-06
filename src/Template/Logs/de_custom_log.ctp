<?php

if (isset($errMsgArr['Formdata'])) {
    $startTime = (isset($errMsgArr['Formdata']['customLogJson']['startTime'])) ? $errMsgArr['Formdata']['customLogJson']['startTime'] : 0;
    $endTime = (isset($errMsgArr['Formdata']['customLogJson']['endTime'])) ? $errMsgArr['Formdata']['customLogJson']['endTime'] : 0;
    $noofImportedRec = (isset($errMsgArr['Formdata']['customLogJson']['totalImported'])) ? $errMsgArr['Formdata']['customLogJson']['totalImported'] : 0;
    $noofErrors = (isset($errMsgArr['Formdata']['customLogJson']['totalIssues'])) ? $errMsgArr['Formdata']['customLogJson']['totalIssues'] : 0;
    $type = 'Formdata';
    
} else {
    $startTime = (isset($data['startTime'])) ? $data['startTime'] : 0;
    $endTime = (isset($data['endTime'])) ? $data['endTime'] : 0;
    $noofImportedRec = (isset($data['totalImported'])) ? $data['totalImported'] : 0;
    $noofErrors = (isset($data['totalIssues'])) ? $data['totalIssues'] : 0;
    $importLink = (isset($data['importLink'])) ? $data['importLink'] : 0;
    $type = _DES;
}
$requestType = (isset($data['type'])) ? $data['type'] : '';
$dbConnName = (isset($params['data'])) ? $params['dbConnName'] : '';
?>
<style>
    .customLog { margin: 20px; padding: 10px; color: #333; font-family: sans-serif; font-size: 15px;}
    h1 { margin-top: 20px; margin-bottom: 0; text-align: left;}
    h1.mainheading { text-align: center; margin: 0; color: #000;}
    hr { color: #eee; border: 1px dashed #aaa;}
    h2.heading { margin-bottom: 5px; color: #333; font-size: 20px;}
    table tr th, table tr td { padding: 5px; text-align: left; font-size: 14px; }

    .summary .details { margin-bottom: 20px;}
    .summary .details table tr td:first-child {width: 30%;}
    .issue table tr th:first-child, .issue table tr td:first-child {width: 30%;}
    .sheetNames { margin-top: 30px; margin-bottom: 20px; padding-bottom: 5px;}
    .summaryHeading { margin-top: 10px;}
    .detailsHeading { margin-top: 30px;}
    .noIssue { text-align: center;}
    .success { color: #4cae4c;}
    .failed { color: #c9302c;}

</style>
<div class="customLog">
    <h1 class="mainheading">Database Administration Log</h1>
    <h1 class="summaryHeading">Summary</h1>
    <hr/>
    <div class="summary">
        <div class="details">
            <table border='0' style="border: 1px; width:100%; text-align:left; border-collapse: collapse;">
                <tr>
                    <td><strong>Module</strong></td>
                    <td><?php echo strtoupper($type) ?></td>
                </tr>
                <?php if (!isset($errMsgArr['Formdata'])) {?>
                <tr>
                    <td><strong>Imported File</strong></td>
                    <td><a href="<?php echo $importLink ?>" target="_blank">Download File</a></td>
                </tr>
                <?php } ?>
                <tr>
                    <td><strong>Database Name</strong></td>
                    <td><?php echo $dbConnName ?></td>
                </tr>
            </table>
        </div>
        <div class="details">
            <table border='0' style="border: 1px; width:100%; text-align:left; border-collapse: collapse;">
                <tbody>
                    <tr>
                        <td><strong>Date</strong></td>
                        <td><?php echo date('Y-m-d') ?></td>
                    </tr>
                    <tr>
                        <td><strong>Start Time</strong></td>
                        <td><?php echo date('H:i:s', strtotime($startTime)) ?></td>
                    </tr>
                    <tr>
                        <td><strong>End Time</strong></td>
                        <td><?php echo date('H:i:s', strtotime($endTime)) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="details">
            <table border='0' style="border: 1px; width:100%; text-align:left; border-collapse: collapse;">
                <tbody>
                    <tr>
                        <td><strong>Total Records</strong></td>
                        <td><strong><?php echo $noofImportedRec + $noofErrors ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong>Imported Records</strong></td>
                        <td class="success"><strong><?php echo $noofImportedRec ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong>Issues</strong></td>
                        <td class="failed"><strong><?php echo $noofErrors ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    if (!empty($errMsgArr) && count($errMsgArr) > 0) { 
    ?>
    <h1 class="detailsHeading">Issue Details</h1>
    <hr/>
    <?php
        foreach ($errMsgArr as $sheetName => $value) {
            $issuuesArray = (isset($value['customLogJson']['issues']) && !empty($value['customLogJson']['issues'])) ? $value['customLogJson']['issues'] : '';
                if ($sheetName != 'Formdata') {
                    if(!empty($issuuesArray) || (isset($value['status']) && $value['status'] == false)) {
                ?>
    <h2 class="heading sheetNames">Sheet : <?php echo $sheetName ?></h2>
                <?php
                    }
                }
                if (!empty($issuuesArray)) {
                ?>
    <div class="issue details">
        <table border='1' style="border: 1px; width:100%; text-align:left; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>Row</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($issuuesArray as $innerValue) { ?>
                <tr>
                    <td><?php echo $innerValue['rowNo'] ?></td>
                    <td><?php echo $innerValue['msg'] ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
                <?php
                }else if(isset($value['status']) && $value['status'] == false) {
                ?>
    <div class="issue details"><?php echo $value['error'] ?></div>
                <?php
                }
        }
    }
    ?>
</div>
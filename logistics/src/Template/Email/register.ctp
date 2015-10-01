<style>


</style>
<div class="customLog">
   
    <div class="summary">
        <div class="details">
            <table border='0' style="border: 0px; width:100%; text-align:left; border-collapse: collapse;">
                <tr>
                    <td style="text-align:left;" colspan='2'>Dear <?php echo ucwords($name) ?>,</td>
                </tr>
                <tr>
                    <td style="text-align:left;width:20px;">&nbsp;</td>
                    <td style="text-align:left;">&nbsp;</td>
                </tr>
               
                <tr>
                    <td style="text-align:left;">Please <a href="<?php echo $website_base_url;?> ">Click here  </a> to login.<strong></strong></td>
                    <td style="text-align:left;"></td>
                </tr>
 <?php if($case=='INSERT'){?>
                <tr>
                    <td style="text-align:left;width:20px;">&nbsp;</td>
                    <td style="text-align:left;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="text-align:left;" >Login Credentials:</td>
                    <td style="text-align:left;" ></td>
                </tr>
                <tr>
                    <td style="text-align:left;width:20px;">&nbsp;</td>
                    <td style="text-align:left;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="text-align:left;50px;" >
                     <table width="50px;" align="left" >
                        <tr>
                         <td style="text-align:left;"  colspan='2'><strong>Username</strong>  : <?php echo $login; ?></td>
                         
                         </tr>

<tr>
                         <td style="text-align:left;"  colspan='2'><strong>Password</strong>  : <?php echo $password; ?></td>
                         
                         </tr>
                        
                    

</table>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td style="text-align:left;">&nbsp;</td>
                    <td style="text-align:left;">&nbsp;</td>
                </tr>
<?php }else{
?>
         <tr>
                    <td style="text-align:left;">&nbsp;</td>
                    <td style="text-align:left;">&nbsp;</td>
                </tr> <tr>
                    <td style="text-align:left;" colspan='2'>Your's information has been updated by System Admin.</td>
                   
                </tr>
 <tr>
                    <td style="text-align:left;">&nbsp;</td>
                    <td style="text-align:left;">&nbsp;</td>
                </tr>
<?php }?>            <tr>
                    <td style="text-align:left;">&nbsp;</td>
                    <td style="text-align:left;">&nbsp;</td>
                </tr>    
                <tr>
                    <td style="text-align:left;">Thank you.<br/>
                    Regards,<br/>
                    OpenEMIS Logistics 
                    </td>
                    <td></td>
                </tr>


             
            </table>
        </div>
      
    </div>
   
</div>
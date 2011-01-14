	 <form action='<?php echo $self; ?>' method='get'>
          <table width='100%' cellpadding='4' cellspacing='0'>
           <tr>
            <td class='subheader' colspan='2'>Choose Installation Type:</td>
           </tr>
           <tr>
            <td><input id='fullinstall' type='radio' name='mode' value='full_install' /></td>
            <td>
             <label for='fullinstall'>Complete PowerDNS install + PDNS-Admin Console install.<br />
             NOTE: Only use if you have NOT already installed PowerDNS as this will destroy all existing database information!
            </td>
           </tr>
           <tr>
            <td><input id='install' type='radio' name='mode' value='new_install' checked='checked' /></td>
            <td><label for='install'>New PDNS-Administrator Console install.</label></td>
           </tr>
           <tr>
            <td><input id='upgrade' type='radio' name='mode' value='upgrade' /></td>
            <td><label for='upgrade'>Upgrade an existing PDNS-Administrator Console.</label></td>
           </tr>
           <tr>
            <td colspan='2' align='center'><br /><input type='submit' value='Continue' /></td>
           </tr>
          </table>
         </form>
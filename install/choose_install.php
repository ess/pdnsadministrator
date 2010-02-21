	 What kind of installation will this be?<br /><br />
	 <form action='<?php echo $self; ?>' method='get'>
          <table border='0' cellpadding='4' cellspacing='0'>
           <tr>
            <td><input id='install' type='radio' name='mode' value='new_install' checked='checked' /></td>
            <td><label for='install'>New PDNS-Admin installation</label></td>
           </tr>
           <tr>
            <td><input id='upgrade' type='radio' name='mode' value='upgrade' /></td>
            <td><label for='upgrade'>Upgrade from a previous version of PDNS-Admin</label></td>
           </tr>
           <tr>
            <td colspan='2' align='center'><br /><input type='submit' value='Continue' /></td>
           </tr>
          </table>
         </form>
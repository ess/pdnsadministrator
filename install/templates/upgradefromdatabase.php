        Upgrade what MySQL database?<?php echo $sets_error; ?><br /><br />
        <form action='<?php echo $this->self; ?>?mode=upgrade&amp;from=<?php echo $this->get['from']; ?>&amp;step=15' method='post'>
        <table border='0' cellpadding='4' cellspacing='0'>
        <tr>
            <td><b>Host Server</b></td>
            <td><input class='input' type='text' name='db_host' value='<?php echo $this->sets['db_host']; ?>' /></td>
        </tr>
        <tr>
            <td><b>Database Name</b></td>
            <td><input class='input' type='text' name='db_name' value='<?php echo $this->sets['db_name']; ?>' /></td>
        </tr>
        <tr>
            <td><b>Database Username</b></td>
            <td><input class='input' type='text' name='db_user' value='<?php echo $this->sets['db_user']; ?>' /></td>
        </tr>
        <tr>
            <td><b>Database Password</b></td>
            <td><input class='input' type='password' name='db_pass' value='' /></td>
        </tr>
        <tr>
            <td><b>Database Port</b><br /><span class='tiny'>Blank for none</span></td>
            <td><input class='input' type='text' name='db_port' value='<?php echo $this->sets['db_port']; ?>' /></td>
        </tr>
        <tr>
            <td><b>Database Socket</b><br /><span class='tiny'>Blank for none</span></td>
            <td><input class='input' type='text' name='db_socket' value='<?php echo $this->sets['db_socket']; ?>' /></td>
        </tr>
        <tr>
            <td colspan='2' class='tiny' align='center'><br /><br />The following should only be changed if you have multiple Quicksilver Forums installed on the same database.</td>
        </tr>
        <tr>
            <td colspan='2' align='center'><br /><input type='submit' value='Continue' /></td>
        </tr>
        </table>
        </form>

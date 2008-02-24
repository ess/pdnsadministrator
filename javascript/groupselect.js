/* Selects all groups for a mass mail from the AdminCP */

function select_all_groups()
{
  opts = document.forms['mailer'].elements['groups[]'].options
  for (i=0; i<opts.length; i++)
  {
    opts[i].selected = true;
  }
}

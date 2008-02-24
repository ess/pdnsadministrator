/* Folderjump, used in PM system */
function get_folder(select,link)
{
  self.location.href = link + '?a=pm&f=' + select.value;
}

function get_forum(select,link)
{
  if(select.value.substring(0, 1) == '.'){
    self.location.href = link + '?c=' + select.value.substring(1, select.value.length);
  }else{
    self.location.href = link + '?a=forum&f=' + select.value;
  }
}

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" 
  "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
<head>
<title>bacula-web</title>
<link rel="stylesheet" type="text/css" href="style/default.css">
</head>
<body>
{include file=header.tpl}

  <div id="nav">
    <a href="index.php" title="{t}Back to the dashboard{/t}">Dashboard</a> >
    <a href="jobs.php" title="{t}Back to the Jobs{/t}">Jobs</a> > Log
  </div>

  <div id="main_center">

  <div class="box">
	<p class="title">{t}Log{/t}</p>

	<table border="0">
	  <tr>
		<td class="tbl_header">{t}Time{/t}</td>
		<td class="tbl_header">{t}Message{/t}</td>
	  </tr>
	{foreach from=$log_lines item=line}
        <tr>
          <td>{$line.time}</td>
          <td class="log_message">{$line.message}</td>
        </tr>
          {/foreach}
        </table>
        <!-- </div> --> <!-- end div class=listbox -->
  </div>

</div>

{include file="footer.tpl"}

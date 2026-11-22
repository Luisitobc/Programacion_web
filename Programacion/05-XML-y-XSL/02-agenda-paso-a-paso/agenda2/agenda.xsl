<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="agenda">
<html>
<head>
  <title>Agenda</title>
  <meta charset="UTF-8"/>
  
</head>
<body>
<h1>Agenda</h1>

<div class="personaje">
  <h3>contacto número: <xsl:value-of select="personaje/@numero"/></h3>
  <ul>
    <li><xsl:value-of select="personaje/nombre"/></li>
    <li><xsl:value-of select="personaje/telefono"/></li>
    <li><xsl:value-of select="personaje/email"/></li>
  </ul>
</div>

</body>
</html>

</xsl:template>
</xsl:stylesheet> 
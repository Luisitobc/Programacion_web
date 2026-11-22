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

<xsl:for-each select="personaje">
<div class="personaje" style="float: left; width: 300px;"  >
  <h3>Personaje número: <xsl:value-of select="@numero"/></h3>
  <div style="width: 70px; float: left;">
     <xsl:element name="img">
       <xsl:attribute name="src">
         <xsl:value-of select="dibujo/@ruta"/>
       </xsl:attribute>
       <xsl:attribute name="height">
         100
       </xsl:attribute>
     </xsl:element>
</div>
  <ul  style="float: left;">
    <li><xsl:value-of select="nombre"/></li>
    <li><xsl:value-of select="telefono"/></li>
    <li><xsl:value-of select="email"/></li>
    <li>
      <a>
        <xsl:attribute name="href">
          <xsl:value-of select="web/@enlace"/>
        </xsl:attribute>
        <xsl:value-of select="web"/>
      </a>
    </li>
  </ul>

</div>
</xsl:for-each>

</body>
</html>
</xsl:template>
</xsl:stylesheet>
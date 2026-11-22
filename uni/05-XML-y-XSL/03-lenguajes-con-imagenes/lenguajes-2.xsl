<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="lenguajes_programacion">

<html>
<head>
<title>Lenguajes de programación</title>
<meta charset="UTF-8"/>
</head>
<body>
<h1>Lenguajes de programación</h1>

<xsl:for-each select="lenguaje">
<div class="lenguaje" style="float: left; width: 520px; height: 300px;">
  <h3>Lenguaje: <xsl:value-of select="@nombre"/></h3>
  <div style="width: 140px; float: left;">
     <xsl:element name="img">
       <xsl:attribute name="src">
         <xsl:value-of select="dibujo/@ruta"/>
       </xsl:attribute>
       <xsl:attribute name="height">
         100
       </xsl:attribute>
     </xsl:element>
  </div>
  <ul style="float: left; width: 320px;">
    <li><xsl:value-of select="anio_creacion"/></li>
    <li><xsl:value-of select="paradigma"/></li>
    <li><xsl:value-of select="tipado"/></li>
    <li><xsl:value-of select="creador"/></li>
    <li><xsl:value-of select="uso_principal"/></li>
    <li>
      <a>
        <xsl:attribute name="href">
          <xsl:value-of select="web/@enlace"/>
        </xsl:attribute>
        <xsl:value-of select="web/@enlace"/>
      </a>
    </li>
  </ul>
</div>
</xsl:for-each>

</body>
</html>
</xsl:template>
</xsl:stylesheet>
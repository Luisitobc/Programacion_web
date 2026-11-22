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

<div class="lenguaje">
  <h3>Lenguaje: <xsl:value-of select="lenguaje/@nombre"/></h3>
  <ul>
    <li>Año de creación: <xsl:value-of select="lenguaje/anio_creacion"/></li>
    <li>Paradigma: <xsl:value-of select="lenguaje/paradigma"/></li>
    <li>Tipado: <xsl:value-of select="lenguaje/tipado"/></li>
    <li>Creador: <xsl:value-of select="lenguaje/creador"/></li>
    <li>Uso principal: <xsl:value-of select="lenguaje/uso_principal"/></li>
  </ul>
</div>

</body>
</html>
</xsl:template>
</xsl:stylesheet>
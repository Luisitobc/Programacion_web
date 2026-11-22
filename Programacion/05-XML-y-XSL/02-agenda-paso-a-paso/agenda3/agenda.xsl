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
			
			<div class="personaje">
				  <h3>Personaje número: <xsl:value-of select="@numero"/></h3>
			  <ul>
			    <li><xsl:value-of select="nombre"/></li>
			    <li><xsl:value-of select="telefono"/></li>
			    <li><xsl:value-of select="email"/></li>
			  </ul>
			</div>
			</xsl:for-each>
		
		</body>
		</html>
	</xsl:template>
</xsl:stylesheet>
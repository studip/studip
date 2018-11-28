<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<!-- vosshe@fh-trier.de -->
<xsl:output method="text" encoding="UTF-8"/>

<xsl:template match="/">
    <xsl:text>&#xFEFF;</xsl:text>
    <xsl:text>Gruppe;</xsl:text>
    <xsl:text>Titel;</xsl:text>
    <xsl:text>Vorname;</xsl:text>
    <xsl:text>Name;</xsl:text>
    <xsl:text>Titel2;</xsl:text>
    <xsl:text>Telefon;</xsl:text>
    <xsl:text>E-Mail</xsl:text>
<xsl:text>
</xsl:text>

    <xsl:for-each select="studip">
	<xsl:for-each select="institut">
	    <xsl:for-each select="personen">
		<xsl:for-each select="gruppe">
		    <xsl:call-template name="showperson"/>
		</xsl:for-each>
	    </xsl:for-each>
	</xsl:for-each>
    </xsl:for-each>
</xsl:template>

<xsl:template name="showperson">
    <xsl:for-each select="person">
	<xsl:text>"</xsl:text>

	<xsl:value-of select="../@key" />
	<xsl:text>";"</xsl:text>

	<xsl:if test="titel">
	    <xsl:value-of select="titel"/>
	</xsl:if>
	<xsl:text>";"</xsl:text>

	<xsl:if test="vorname">
	    <xsl:value-of select="vorname"/>
	</xsl:if>
	<xsl:text>";"</xsl:text>

	<xsl:if test="nachname">
	    <xsl:value-of select="nachname"/>
	</xsl:if>
	<xsl:text>";"</xsl:text>

	<xsl:if test="titel2">
	    <xsl:value-of select="titel2"/>
	</xsl:if>
	<xsl:text>";"</xsl:text>

	<xsl:if test="telefon">
	    <xsl:value-of select="telefon"/>
	</xsl:if>
	<xsl:text>";"</xsl:text>

	<xsl:if test="email">
	    <xsl:value-of select="email"/>
	</xsl:if>
	<xsl:text>"</xsl:text>

<xsl:text>
</xsl:text>

    </xsl:for-each>
</xsl:template>

</xsl:stylesheet>

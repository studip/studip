<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<!-- vosshe@fh-trier.de -->
<xsl:output method="text" encoding="UTF-8"/>
    <xsl:template match="/">
    <xsl:text>&#xFEFF;</xsl:text>
	<xsl:text>Typ;</xsl:text>
	<xsl:text>Dozenten;</xsl:text>
	<xsl:text>Titel;</xsl:text>
	<xsl:text>max. Teilnehmende;</xsl:text>
	<xsl:text>ECTS;</xsl:text>
	<xsl:text>Termin</xsl:text>
<xsl:text>
</xsl:text>

	<xsl:for-each select="studip">
	    <xsl:for-each select="institut">
		<xsl:for-each select="seminare">
		    <xsl:for-each select="gruppe">
			<xsl:call-template name="showseminar" />
    		    </xsl:for-each>
    		</xsl:for-each>
	    </xsl:for-each>
	</xsl:for-each>

</xsl:template>

<xsl:template name="showseminar">
    <xsl:for-each select="seminar">
    	<xsl:text>"</xsl:text>

	<xsl:value-of select="../@key" />
	<xsl:text>";"</xsl:text>

	<xsl:for-each select="dozenten/dozent">
	    <xsl:if test="position() &gt; 1">
		<xsl:text>, </xsl:text>
	    </xsl:if>
	    <xsl:value-of select="."/>
	</xsl:for-each>
	<xsl:text>";"</xsl:text>

	<xsl:value-of select="titel"/>
	<xsl:text>";"</xsl:text>

	<xsl:if test="teilnehmerzahl">
	    <xsl:value-of select="normalize-space(teilnehmerzahl)"/>
	</xsl:if>
	<xsl:text>";"</xsl:text>

	<xsl:if test="ects">
	    <xsl:value-of select="ects"/>
	</xsl:if>
	<xsl:text>";"</xsl:text>

	<xsl:if test="termine/termin">
	    <xsl:value-of select="termine/termin"/>
	</xsl:if>
	<xsl:text>"</xsl:text>

	<xsl:text>
</xsl:text>
    </xsl:for-each>
</xsl:template>

</xsl:stylesheet>

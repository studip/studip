<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
	<xsl:output method="text" encoding="UTF-8"/>
	<xsl:template match="/">
<xsl:text>{\rtf1\ansi\ansicpg1252\deff0\deflang1031{\fonttbl{\f0\fnil\fcharset0 Times New Roman;}}
\viewkind4\uc1\pard</xsl:text>

		<xsl:for-each select="studip">
				<xsl:text>\par\fs36 Veranstaltung: </xsl:text><xsl:value-of select="@range"/>
			<xsl:for-each select="institut"><xsl:text>
\par</xsl:text>
				<xsl:if test="personen">
			<xsl:text>
\par\fs28 Anmeldeliste
\par
\par\trowd \trgaph70\trleft-70\trbrdrt\brdrs\brdrw10 \trbrdrl\brdrs\brdrw10 \trbrdrb\brdrs\brdrw10 \trbrdrr\brdrs\brdrw10 \trbrdrh\brdrs\brdrw10 \trbrdrv\brdrs\brdrw10 \clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr
\brdrs\brdrw10 \cltxlrtb \cellx2233\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx4536\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr
\brdrs\brdrw10 \cltxlrtb \cellx5839\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx6839\pard \nowidctlpar\widctlpar\intbl\adjustright {Name\cell Telefon\cell E-Mail\cell Bemerkung\cell }\pard \nowidctlpar\widctlpar\intbl\adjustright {\row }\pard</xsl:text>

						<xsl:choose>
							<xsl:when test="personen/gruppe">
								<xsl:for-each select="personen/gruppe">
								<xsl:call-template name="showperson"/>
								</xsl:for-each>
							</xsl:when>
							<xsl:otherwise>
								<xsl:for-each select="personen">
									<xsl:call-template name="showperson"/>
								</xsl:for-each>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:if>
					<xsl:text>\par</xsl:text>
					<xsl:if test="studiengaenge/studiengang">
						<xsl:text>\fs28\b Studiengänge:\b0</xsl:text>
						<xsl:for-each select="studiengaenge/studiengang">
							<xsl:text>\par\fs24</xsl:text><xsl:value-of select="name"/><xsl:text>: </xsl:text><xsl:value-of select="anzahl"/>
						</xsl:for-each>
						<xsl:text>\par</xsl:text>
					</xsl:if>
				</xsl:for-each>
\par\qr\fs16 Generiert von Stud.IP Version <xsl:value-of select="@version"/>
			</xsl:for-each>
		<xsl:text> }</xsl:text>
	</xsl:template>

<xsl:template name="showperson">
	<xsl:for-each select="person">
			<xsl:text>
\trowd \trgaph70\trleft-70\trbrdrt\brdrs\brdrw10 \trbrdrl\brdrs\brdrw10 \trbrdrb\brdrs\brdrw10 \trbrdrr\brdrs\brdrw10 \trbrdrh\brdrs\brdrw10 \trbrdrv\brdrs\brdrw10 \clvertalt\clbrdrt
\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx2233\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx4536\clvertalt\clbrdrt
\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx5839\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx6839\pard\plain \nowidctlpar\widctlpar\intbl\adjustright \lang1031\cgrid {\fs24</xsl:text>
				<xsl:if test="titel">
					<xsl:value-of select="titel"/>
					<xsl:text> </xsl:text>
				</xsl:if>
				<xsl:value-of select="nachname"/>
				<xsl:text>, </xsl:text>
				<xsl:value-of select="vorname"/>
				<xsl:if test="titel2">
					<xsl:text> </xsl:text>
					<xsl:value-of select="titel2"/>
				</xsl:if>
<xsl:text>\cell </xsl:text>
				<xsl:if test="privatnummer">
					<xsl:value-of select="privatnummer"/>
				</xsl:if>
<xsl:text>\cell </xsl:text>
				<xsl:if test="email">
					<xsl:value-of select="email"/>
				</xsl:if>
<xsl:text>\cell </xsl:text>
				<xsl:if test="bemerkung">
					<xsl:value-of select="bemerkung"/>
				</xsl:if>
<xsl:text>\cell }\pard \nowidctlpar\widctlpar\intbl\adjustright {\row }\pard</xsl:text>
	</xsl:for-each>
</xsl:template>
</xsl:stylesheet>

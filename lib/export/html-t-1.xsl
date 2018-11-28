<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
	<xsl:output method="html" encoding="UTF-8"/>
	<xsl:template match="/">
	<html>
        <meta charset="utf-8"/>
		<body>
		<xsl:for-each select="studip">
				<h1>Veranstaltung: <xsl:value-of select="@range"/></h1>
			<xsl:for-each select="institut">
				<br/>
				<xsl:if test="personen">
					<table width="100%" cellpadding="5" cellspacing="2">
						<tr colspan="5">
							<td>
								<h2>Teilnehmendenliste</h2>
							</td>
						</tr>
						<tr>
							<td>
								<br/>
							</td>
						</tr>
						<tr>
							<td bgcolor="#006699">
								<b>
									<font color="#FFFFFF">Name</font>
									</b>
								</td>
								<td bgcolor="#006699">
									<b>
										<font color="#FFFFFF">Telefon</font>
									</b>
								</td>
								<td bgcolor="#006699">
									<b>
										<font color="#FFFFFF">Adresse</font>
									</b>
								</td>
								<td bgcolor="#006699">
									<b>
										<font color="#FFFFFF">E-Mail</font>
									</b>
								</td>
							</tr>
						<xsl:choose>
							<xsl:when test="personen/gruppe">
								<xsl:for-each select="personen/gruppe">
									<tr>
										<td colspan="4" bgcolor="#006699">
											<font color="#FFFFFF">
												<b>
													<xsl:value-of select="@key"/>
												</b>
											</font>
										</td>
									</tr>
								<xsl:call-template name="showperson"/>
								</xsl:for-each>
							</xsl:when>
							<xsl:otherwise>
								<xsl:for-each select="personen">
									<xsl:call-template name="showperson"/>
								</xsl:for-each>
							</xsl:otherwise>
						</xsl:choose>
						</table>
					</xsl:if>
					<br/>
					<br/>
				</xsl:for-each>
				<font size="-1">Generiert von Stud.IP Version <xsl:value-of select="@version"/></font>
			</xsl:for-each>
			</body>
		</html>
	</xsl:template>

<xsl:template name="showperson">
	<xsl:for-each select="person">
		<tr>
			<td bgcolor="#EEEEEE">
				<xsl:if test="titel">
					<xsl:value-of select="titel"/>
					<xsl:text> </xsl:text>
				</xsl:if>
				<xsl:value-of select="vorname"/>
				<xsl:text> </xsl:text>
				<xsl:value-of select="nachname"/>
				<xsl:if test="titel2">
					<xsl:text> </xsl:text>
					<xsl:value-of select="titel2"/>
				</xsl:if>
				<br/>
			</td>
			<td bgcolor="#EEEEEE">
				<xsl:if test="privadr">
					<xsl:value-of select="privadr"/>
				</xsl:if>
				<br/>
			</td>
			<td bgcolor="#EEEEEE">
				<xsl:if test="privatnr">
					<xsl:value-of select="privatnr"/>
				</xsl:if>
				<br/>
			</td>
			<td bgcolor="#EEEEEE">
				<xsl:if test="email">
					<xsl:value-of select="email"/>
				</xsl:if>
				<br/>
			</td>
		</tr>
	</xsl:for-each>
</xsl:template>
</xsl:stylesheet>

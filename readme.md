# RepoMe

Access posts based on modified date, for use with clients who cache data and need to request specific updates.

## Notes

* datefrom - The start date (based on 'modified')
* dateto - The end date (based on 'modified')
* posttype - The type of post to return e.g. post, page or a custom post type
* customfields[] - The custom fields to include in the response

## Example response

* /repome?datefrom=2012-08-19&posttype=post&customfields[]=myfield&customfields[]=myfield2

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<posts>
	<post id="4" url="http://www.mywordpress.com/myarticle" modified="2012-08-09 13:41:45" published="2012-08-13 12:00:47">
		<title>My Article</title>
		<author>Craig Rowe</author>
		<image src="http://www.mywordpress.com/myfeatureimage.jpg" />
		<abstract><![CDATA[My abstract]]></abstract>
		<body><![CDATA[<p>my body</p>]]></body>
		<categories value="articles featured"></categories>
		<fields>
			<myfield>myvalue</myfield>
			<myfield2>myvalue2</myfield2>
		</fields>
	</post>
	<post id="1" status="deleted"></post>
	<post id="2" status="unpublished"></post>
	<post id="3" status="unpublished">
</posts>
```
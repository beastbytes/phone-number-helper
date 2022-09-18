# Phone Number Helper (phone-number-helper)
Static helper methods for phone numbers:

* Format national phone numbers to the country's [national numbering plan](https://www.itu.int/oth/T0202.aspx?parent=T0202)
* Convert [ITU-T Recommendation E.123](https://www.itu.int/rec/T-REC-E.123) format numbers to [EPP](https://www.rfc-editor.org/rfc/rfc4933.html#section-2.5) format

For license information see the [LICENSE](LICENSE.md) file.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist beastbytes/phone-number-helper
```

or add

```json
"beastbytes/phone-number-helper": "^1.0.0"
```

to the require section of your composer.json.

A n6lPhoneNumberDataInterface implementation is also required, e.g. beastbytes/n6l-phone-number-data-php

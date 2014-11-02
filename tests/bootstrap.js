var x = require('casper').selectXPath;

var site ='http://127.0.0.1/wordpress';
if (casper.cli.has('uri')) {
  site = 'http://' + casper.cli.get('uri');
}
casper.test.begin('Wordpress is installed succesfully', 0, function() {
});
casper.start(site, function() {
    var header1 = this.evaluate(function() {
        return document.querySelector('#post-1 header h1 a').innerHTML;
    });
    this.echo(header1);
    this.test.assertEquals(header1, "Hello world!", 'Index page is loaded.');
});
casper.run(function() {
    this.test.done();
});

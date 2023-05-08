const { readdirSync, rmSync } = require('fs');
const dir = './builds';
var AdmZip = require("adm-zip");
var zip = new AdmZip();
zip.addLocalFolder('./ipos');
readdirSync(dir).forEach(f => rmSync(`${dir}/${f}`));
zip.writeZip('./builds/ipos.zip')
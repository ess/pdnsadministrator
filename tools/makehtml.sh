#!/bin/bash

# Use txt2tags to build the html doc files. Use header.html and footer.html
# in htmldoc to make them all pretty

cat  htmldoc/header.html > ../docs/html/index.html
cat htmldoc/indexheader.html >> ../docs/html/index.html
perl reformatwiki.pl < htmldoc/index.txt > INDEX.txt
txt2tags --target=xhtml --no-headers --outfile=- INDEX.txt >> ../docs/html/index.html
cat htmldoc/footer.html >> ../docs/html/index.html

cat htmldoc/header.html > ../docs/html/readme.html
cat htmldoc/readmeheader.html >> ../docs/html/readme.html
perl reformatwiki.pl < ../docs/README.txt > README.txt
txt2tags --target=xhtml --no-headers --outfile=- README.txt >> ../docs/html/readme.html
cat htmldoc/footer.html >> ../docs/html/readme.html

cat htmldoc/header.html > ../docs/html/faq.html
cat htmldoc/faqheader.html >> ../docs/html/faq.html
perl reformatwiki.pl < ../docs/FAQ.txt > FAQ.txt
txt2tags --target=xhtml --no-headers --outfile=- FAQ.txt >> ../docs/html/faq.html
cat htmldoc/footer.html >> ../docs/html/faq.html

cat htmldoc/header.html > ../docs/html/install.html
cat htmldoc/installheader.html >> ../docs/html/install.html
perl reformatwiki.pl < ../docs/INSTALL.txt > INSTALL.txt
txt2tags --target=xhtml --no-headers --outfile=- INSTALL.txt >> ../docs/html/install.html
cat htmldoc/footer.html >> ../docs/html/install.html

rm INDEX.txt
rm README.txt
rm FAQ.txt
rm INSTALL.txt


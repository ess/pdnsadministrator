#!/usr/bin/perl

# Used to run some basic search and replaces on the wiki file before
# giving it to txt2tags

while (defined($line = <STDIN>)) {
	$line =~ s/^\* /- /g;
	$line =~ s/^==/\n==/g;
	$line =~ s/^    \./\n\t/g;
	$line =~ s/^  /\n/g;
	$line =~ s/<\/?tt>/``/g;
	$line =~ s/\[http([^\s\]]+)\s([^\]]+)]/[\2 http\1]/g;
	print $line;
}

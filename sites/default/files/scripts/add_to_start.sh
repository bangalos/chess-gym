perl -pi -e 'print "[Premove \"0\"]\n" if ($. == 1); close ARGV if eof;' game_*

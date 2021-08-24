<?php /**
* Strip the audio entries from the RSS feeds from TastyTrade.com
*/
new tt_rss;

class tt_rss
{
	protected $feeds = array(
		'market-measures' => 'https://feeds.tastytrade.com/podcast/shows/MT.rss',
		'options-jive' => 'https://feeds.tastytrade.com/podcast/shows/option-jive.rss',
	);

	function __construct()
	{
                foreach ($this->feeds as $feed => $url)
                {
                        $this->generate($feed, $url);
                }
	}

	protected function generate($feed_id, $url)
	{
		$local = __DIR__ . "/docs/{$feed_id}.rss";

                echo "download: {$url}\n";
		$tmp = file_get_contents($url);
                file_put_contents(__DIR__ . "/last.{$feed_id}.rss", $tmp);

		// strip audio
		//
		preg_match_all('~<item>.+</item>~Uis', $tmp, $R);
		foreach ($R[0] as $i => $match)
		{
			if (preg_match('~(\.m4a|\.mp3)~', $match))
			{
                                echo "stripping audio...\n";
				$tmp = str_replace($match, '', $tmp);
			}
		}

		// replace images
		//
		preg_match_all('~\<itunes\:image href\="[^"]+"/\>~Uis', $tmp, $R);
		foreach ($R[0] as $i => $match)
		{
			$tmp = str_replace(
				$match,
				'<itunes:image href="http://f33ds.kaloyan.info/' . $feed_id . '.png"/>',
				$tmp);
		}

		file_put_contents($local, $tmp);

                chdir(__DIR__);
                shell_exec('git add docs/* last.*.rss');
                shell_exec('git commit -m "' . gmdate('c') . '" ');
		shell_exec('git push');
	}
}

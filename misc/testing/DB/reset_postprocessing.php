<?php

require_once dirname(__DIR__, 3).DIRECTORY_SEPARATOR.'bootstrap/autoload.php';

use App\Models\Category;
use Blacklight\ColorCLI;
use Blacklight\ConsoleTools;
use Illuminate\Support\Facades\DB;

$pdo = DB::connection()->getPdo();
$consoletools = new ConsoleTools();
$colorCli = new ColorCLI();
$ran = false;

if (isset($argv[1]) && $argv[1] === 'all' && isset($argv[2]) && $argv[2] === 'true') {
    $ran = true;
    $where = '';
    if (isset($argv[3]) && $argv[3] === 'truncate') {
        echo 'Truncating tables';
        $pdo->exec('TRUNCATE TABLE consoleinfo');
        $pdo->exec('TRUNCATE TABLE gamesinfo');
        $pdo->exec('TRUNCATE TABLE movieinfo');
        $pdo->exec('TRUNCATE TABLE video_data');
        $pdo->exec('TRUNCATE TABLE musicinfo');
        $pdo->exec('TRUNCATE TABLE bookinfo');
        $pdo->exec('TRUNCATE TABLE release_nfos');
        $pdo->exec('TRUNCATE TABLE releaseextrafull');
        $pdo->exec('TRUNCATE TABLE xxxinfo');
        $pdo->exec('TRUNCATE TABLE videos');
        $pdo->exec('TRUNCATE TABLE videos_aliases');
        $pdo->exec('TRUNCATE TABLE tv_info');
        $pdo->exec('TRUNCATE TABLE tv_episodes');
        $pdo->exec('TRUNCATE TABLE anidb_info');
        $pdo->exec('TRUNCATE TABLE anidb_episodes');
    }
    $colorCli->header('Resetting all postprocessing');
    $qry = $pdo->query('SELECT id FROM releases');
    $affected = 0;
    if ($qry instanceof \Traversable) {
        $total = $qry->rowCount();
        foreach ($qry as $releases) {
            $pdo->exec(
                sprintf(
                    '
						UPDATE releases
						SET consoleinfo_id = NULL, gamesinfo_id = 0, imdbid = NULL, musicinfo_id = NULL,
							bookinfo_id = NULL, videos_id = 0, tv_episodes_id = 0, xxxinfo_id = 0, passwordstatus = -1, haspreview = -1,
							jpgstatus = 0, videostatus = 0, audiostatus = 0, nfostatus = -1
						WHERE id = %d',
                    $releases['id']
                )
            );
            $consoletools->overWritePrimary('Resetting Releases:  '.$consoletools->percentString(++$affected, $total));
        }
    }
}
if (isset($argv[1]) && ($argv[1] === 'consoles' || $argv[1] === 'all')) {
    $ran = true;
    if (isset($argv[3]) && $argv[3] === 'truncate') {
        $pdo->exec('TRUNCATE TABLE consoleinfo');
    }
    if (isset($argv[2]) && $argv[2] === 'true') {
        $colorCli->header('Resetting all Console postprocessing');
        $where = ' WHERE consoleinfo_id IS NOT NULL';
    } else {
        $colorCli->header('Resetting all failed Console postprocessing');
        $where = ' WHERE consoleinfo_id IN (-2, 0) AND categories_id BETWEEN '.Category::GAME_ROOT.' AND '.Category::GAME_OTHER;
    }

    $qry = $pdo->query('SELECT id FROM releases'.$where);
    if ($qry !== false) {
        $total = $qry->rowCount();
    } else {
        $total = 0;
    }
    $concount = 0;
    if ($qry instanceof \Traversable) {
        foreach ($qry as $releases) {
            $pdo->exec('UPDATE releases SET consoleinfo_id = NULL WHERE id = '.$releases['id']);
            $consoletools->overWritePrimary('Resetting Console Releases:  '.$consoletools->percentString(++$concount, $total));
        }
    }
    $colorCli->header(PHP_EOL.number_format($concount).' consoleinfoIDs reset.');
}
if (isset($argv[1]) && ($argv[1] === 'games' || $argv[1] === 'all')) {
    $ran = true;
    if (isset($argv[3]) && $argv[3] === 'truncate') {
        $pdo->exec('TRUNCATE TABLE gamesinfo');
    }
    if (isset($argv[2]) && $argv[2] === 'true') {
        $colorCli->header('Resetting all Games postprocessing');
        $where = ' WHERE gamesinfo_id != 0';
    } else {
        $colorCli->header('Resetting all failed Games postprocessing');
        $where = ' WHERE gamesinfo_id IN (-2, 0) AND categories_id = 4050';
    }

    $qry = $pdo->query('SELECT id FROM releases'.$where);

    $total = 0;
    if ($qry !== false) {
        $total = $qry->rowCount();
    }

    $concount = 0;
    if ($qry instanceof \Traversable) {
        foreach ($qry as $releases) {
            $pdo->exec('UPDATE releases SET gamesinfo_id = 0 WHERE id = '.$releases['id']);
            $consoletools->overWritePrimary('Resetting Games Releases:  '.$consoletools->percentString(++$concount, $total));
        }
        $colorCli->header(PHP_EOL.number_format($concount).' gameinfo_IDs reset.');
    }
}
if (isset($argv[1]) && ($argv[1] === 'movies' || $argv[1] === 'all')) {
    $ran = true;
    if (isset($argv[3]) && $argv[3] === 'truncate') {
        $pdo->exec('TRUNCATE TABLE movieinfo');
    }
    if (isset($argv[2]) && $argv[2] === 'true') {
        $colorCli->header('Resetting all Movie postprocessing');
        $where = ' WHERE imdbid IS NOT NULL';
    } else {
        $colorCli->header('Resetting all failed Movie postprocessing');
        $where = ' WHERE imdbid IN (-2, 0) AND categories_id BETWEEN '.Category::MOVIE_ROOT.' AND '.Category::MOVIE_OTHER;
    }

    $qry = $pdo->query('SELECT id FROM releases'.$where);
    if ($qry !== false) {
        $total = $qry->rowCount();
    } else {
        $total = 0;
    }
    $concount = 0;
    if ($qry instanceof \Traversable) {
        foreach ($qry as $releases) {
            $pdo->exec('UPDATE releases SET imdbid = NULL WHERE id = '.$releases['id']);
            $consoletools->overWritePrimary('Resetting Movie Releases:  '.$consoletools->percentString(++$concount, $total));
        }
    }
    $colorCli->header(PHP_EOL.number_format($concount).' imdbIDs reset.');
}
if (isset($argv[1]) && ($argv[1] === 'music' || $argv[1] === 'all')) {
    $ran = true;
    if (isset($argv[3]) && $argv[3] === 'truncate') {
        $pdo->exec('TRUNCATE TABLE musicinfo');
    }
    if (isset($argv[2]) && $argv[2] === 'true') {
        $colorCli->header('Resetting all Music postprocessing');
        $where = ' WHERE musicinfo_id IS NOT NULL';
    } else {
        $colorCli->header('Resetting all failed Music postprocessing');
        $where = ' WHERE musicinfo_id IN (-2, 0) AND categories_id BETWEEN '.Category::MUSIC_ROOT.' AND '.Category::MUSIC_OTHER;
    }

    $qry = $pdo->query('SELECT id FROM releases'.$where);
    $total = $qry->rowCount();
    $concount = 0;
    if ($qry instanceof \Traversable) {
        foreach ($qry as $releases) {
            $pdo->exec(sprintf('UPDATE releases SET musicinfo_id = NULL WHERE id = %s ', $releases['id']));
            $consoletools->overWritePrimary('Resetting Music Releases:  '.$consoletools->percentString(++$concount, $total));
        }
    }
    $colorCli->header(PHP_EOL.number_format($concount).' musicinfo_ids reset.');
}
if (isset($argv[1]) && ($argv[1] === 'misc' || $argv[1] === 'all')) {
    $ran = true;
    if (isset($argv[2]) && $argv[2] === 'true') {
        $colorCli->header('Resetting all Additional postprocessing');
        $where = ' WHERE (haspreview != -1 AND haspreview != 0) OR (passwordstatus != -1 AND passwordstatus != 0) OR jpgstatus != 0 OR videostatus != 0 OR audiostatus != 0';
    } else {
        $colorCli->header('Resetting all failed Additional postprocessing');
        $where = ' WHERE haspreview < -1 OR haspreview = 0 OR passwordstatus < -1 OR passwordstatus = 0 OR jpgstatus < 0 OR videostatus < 0 OR audiostatus < 0';
    }

    $where .= ' AND categories_id < 1000';

    $colorCli->primary('SELECT id FROM releases'.$where);
    $qry = $pdo->query('SELECT id FROM releases'.$where);
    if ($qry !== false) {
        $total = $qry->rowCount();
    } else {
        $total = 0;
    }
    $concount = 0;
    if ($qry instanceof \Traversable) {
        foreach ($qry as $releases) {
            $pdo->exec('UPDATE releases SET passwordstatus = -1, haspreview = -1, jpgstatus = 0, videostatus = 0, audiostatus = 0 WHERE id = '.$releases['id']);
            $consoletools->overWritePrimary('Resetting Releases:  '.$consoletools->percentString(++$concount, $total));
        }
    }
    $colorCli->header(PHP_EOL.number_format($concount).' Releases reset.');
}
if (isset($argv[1]) && ($argv[1] === 'tv' || $argv[1] === 'all')) {
    $ran = true;
    if (isset($argv[3]) && $argv[3] === 'truncate') {
        $pdo->exec('DELETE v, va FROM videos v INNER JOIN videos_aliases va ON v.id = va.videos_id WHERE type = 0');
        $pdo->exec('TRUNCATE TABLE tv_info');
        $pdo->exec('TRUNCATE TABLE tv_episodes');
    }
    if (isset($argv[2]) && $argv[2] === 'true') {
        $colorCli->header('Resetting all TV postprocessing');
        $where = ' WHERE videos_id != 0 AND tv_episodes_id != 0 AND categories_id BETWEEN '.Category::TV_ROOT.' AND '.Category::TV_OTHER;
    } else {
        $colorCli->header('Resetting all failed TV postprocessing');
        $where = ' WHERE tv_episodes_id < 0 AND categories_id BETWEEN '.Category::TV_ROOT.' AND '.Category::TV_OTHER;
    }

    $qry = $pdo->query('SELECT id FROM releases'.$where);
    if ($qry !== false) {
        $total = $qry->rowCount();
    } else {
        $total = 0;
    }
    $concount = 0;
    if ($qry instanceof \Traversable) {
        foreach ($qry as $releases) {
            $pdo->exec('UPDATE releases SET videos_id = 0, tv_episodes_id = 0 WHERE id = '.$releases['id']);
            $consoletools->overWritePrimary('Resetting TV Releases:  '.$consoletools->percentString(++$concount, $total));
        }
    }
    $colorCli->header(PHP_EOL.number_format($concount).' Video IDs reset.');
}
if (isset($argv[1]) && ($argv[1] === 'anime' || $argv[1] === 'all')) {
    $ran = true;
    if (isset($argv[3]) && $argv[3] === 'truncate') {
        $pdo->exec('TRUNCATE TABLE anidb_info');
        $pdo->exec('TRUNCATE TABLE anidb_episodes');
    }
    if (isset($argv[2]) && $argv[2] === 'true') {
        $colorCli->header('Resetting all Anime postprocessing');
        $where = ' WHERE categories_id = 5070';
    } else {
        $colorCli->header('Resetting all failed Anime postprocessing');
        $where = ' WHERE anidbid BETWEEN -2 AND -1 AND categories_id = '.Category::TV_ANIME;
    }

    $qry = $pdo->query('SELECT id FROM releases'.$where);
    if ($qry !== false) {
        $total = $qry->rowCount();
    } else {
        $total = 0;
    }
    $concount = 0;
    if ($qry instanceof \Traversable) {
        foreach ($qry as $releases) {
            $pdo->exec('UPDATE releases SET anidbid = NULL WHERE id = '.$releases['id']);
            $consoletools->overWritePrimary('Resetting Anime Releases:  '.$consoletools->percentString(++$concount, $total));
        }
    }
    $colorCli->header(PHP_EOL.number_format($concount).' anidbIDs reset.');
}
if (isset($argv[1]) && ($argv[1] === 'books' || $argv[1] === 'all')) {
    $ran = true;
    if (isset($argv[3]) && $argv[3] === 'truncate') {
        $pdo->exec('TRUNCATE TABLE bookinfo');
    }
    if (isset($argv[2]) && $argv[2] === 'true') {
        $colorCli->header('Resetting all Book postprocessing');
        $where = ' WHERE bookinfo_id IS NOT NULL';
    } else {
        $colorCli->header('Resetting all failed Book postprocessing');
        $where = ' WHERE bookinfo_id IN (-2, 0) AND categories_id BETWEEN '.Category::BOOKS_ROOT.' AND '.Category::BOOKS_UNKNOWN;
    }

    $qry = $pdo->query('SELECT id FROM releases'.$where);
    $total = $qry->rowCount();
    $concount = 0;
    if ($qry instanceof \Traversable) {
        foreach ($qry as $releases) {
            $pdo->exec('UPDATE releases SET bookinfo_id = NULL WHERE id = '.$releases['id']);
            $consoletools->overWritePrimary('Resetting Book Releases:  '.$consoletools->percentString(++$concount, $total));
        }
    }
    $colorCli->header(PHP_EOL.number_format($concount).' bookinfoIDs reset.');
}
if (isset($argv[1]) && ($argv[1] === 'xxx' || $argv[1] === 'all')) {
    $ran = true;
    if (isset($argv[3]) && $argv[3] === 'truncate') {
        $pdo->exec('TRUNCATE TABLE xxxinfo');
    }
    if (isset($argv[2]) && $argv[2] === 'true') {
        $colorCli->header('Resetting all XXX postprocessing');
        $where = ' WHERE xxxinfo_id != 0';
    } else {
        $colorCli->header('Resetting all failed XXX postprocessing');
        $where = ' WHERE xxxinfo_id IN (-2, 0) AND categories_id BETWEEN '.Category::XXX_ROOT.' AND '.Category::XXX_X264;
    }

    $qry = $pdo->query('SELECT id FROM releases'.$where);
    $concount = 0;
    if ($qry instanceof \Traversable) {
        $total = $qry->rowCount();
        foreach ($qry as $releases) {
            $pdo->exec('UPDATE releases SET xxxinfo_id = 0 WHERE id = '.$releases['id']);
            $consoletools->overWritePrimary('Resetting XXX Releases:  '.$consoletools->percentString(
                ++$concount,
                    $total
            ));
        }
    }
    $colorCli->header(PHP_EOL.number_format($concount).' xxxinfo_IDs reset.');
}
if (isset($argv[1]) && ($argv[1] === 'nfos' || $argv[1] === 'all')) {
    $ran = true;
    if (isset($argv[3]) && $argv[3] === 'truncate') {
        $pdo->exec('TRUNCATE TABLE release_nfos');
    }
    if (isset($argv[2]) && $argv[2] === 'true') {
        $colorCli->header('Resetting all NFO postprocessing');
        $where = ' WHERE nfostatus != -1';
    } else {
        $colorCli->header('Resetting all failed NFO postprocessing');
        $where = ' WHERE nfostatus < -1';
    }

    $qry = $pdo->query('SELECT id FROM releases'.$where);
    $concount = 0;
    if ($qry instanceof \Traversable) {
        $total = $qry->rowCount();
        foreach ($qry as $releases) {
            $pdo->exec('UPDATE releases SET nfostatus = -1 WHERE id = '.$releases['id']);
            $consoletools->overWritePrimary('Resetting NFO Releases:  '.$consoletools->percentString(++$concount, $total));
        }
    }
    $colorCli->header(PHP_EOL.number_format($concount).' NFOs reset.');
}

if ($ran === false) {
    exit(
        $colorCli->error(
            '\nThis script will reset postprocessing per category. It can also truncate the associated tables.'
            .'\nTo reset only those that have previously failed, those without covers, samples, previews, etc. use the '
            .'second argument false.\n'
            .'To reset even those previously post processed, use the second argument true.'.PHP_EOL
            .'To truncate the associated table, use the third argument truncate.'.PHP_EOL.PHP_EOL
            .'php reset_postprocessing.php consoles true    ...: To reset all consoles.'.PHP_EOL
            .'php reset_postprocessing.php games true       ...: To reset all games.'.PHP_EOL
            .'php reset_postprocessing.php movies true      ...: To reset all movies.'.PHP_EOL
            .'php reset_postprocessing.php music true       ...: To reset all music.'.PHP_EOL
            .'php reset_postprocessing.php misc true        ...: To reset all misc.'.PHP_EOL
            .'php reset_postprocessing.php tv true          ...: To reset all tv.'.PHP_EOL
            .'php reset_postprocessing.php anime true       ...: To reset all anime.'.PHP_EOL
            .'php reset_postprocessing.php books true       ...: To reset all books.'.PHP_EOL
            .'php reset_postprocessing.php xxx true         ...: To reset all xxx.'.PHP_EOL
            .'php reset_postprocessing.php nfos true        ...: To reset all nfos.'.PHP_EOL
            .'php reset_postprocessing.php all true         ...: To reset everything.'.PHP_EOL
        )
    );
}
echo PHP_EOL;

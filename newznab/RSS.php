<?php

namespace newznab;

use newznab\db\Settings;

/**
 * Class RSS -- contains specific functions for RSS
 *
 * @package newznab
 */
Class RSS
{
	/** Releases class
	 * @var Releases
	 */
	public $releases;

	/** Settings class
	 * @var \newznab\db\Settings
	 */
	public $pdo;

	/**
	 * @param array $options
	 */
	public function __construct(array $options = [])
	{
		$defaults = [
				'Settings' => null,
				'Releases' => null
		];
		$options += $defaults;

		$this->pdo = ($options['Settings'] instanceof Settings ? $options['Settings'] : new Settings());
		$this->releases = ($options['Releases'] instanceof Releases ? $options['Releases'] : new Releases());
	}

	/**
	 * Get releases for RSS.
	 *
	 * @param     $cat
	 * @param int $offset
	 * @param int $userID
	 * @param int $videosId
	 * @param int $aniDbID
	 * @param int $airDate
	 *
	 * @return array
	 */
	public function getRss($cat, $offset, $videosId, $aniDbID, $userID = 0, $airDate = -1)
	{
		$catSearch = $cartSearch = '';

		$catLimit = "AND r.categoryid BETWEEN 5000 AND 5999";

		if (count($cat)) {
			if ($cat[0] == -2) {
				$cartSearch = sprintf(' INNER JOIN usercart ON usercart.userid = %d AND usercart.releaseid = r.id ', $userID);
			} else if ($cat[0] != -1) {
				$catSearch = $this->releases->categorySQL($cat);
			}
		}

		$sql = $this->pdo->query(
				sprintf(
						"SELECT r.*, m.cover, m.imdbid, m.rating, m.plot,
					m.year, m.genre, m.director, m.actors, g.name AS group_name,
					CONCAT(cp.title, ' > ', c.title) AS category_name,
					%s AS category_ids,
					COALESCE(cp.id,0) AS parentCategoryid,
					mu.title AS mu_title, mu.url AS mu_url, mu.artist AS mu_artist,
					mu.publisher AS mu_publisher, mu.releasedate AS mu_releasedate,
					mu.review AS mu_review, mu.tracks AS mu_tracks, mu.cover AS mu_cover,
					mug.title AS mu_genre, co.title AS co_title, co.url AS co_url,
					co.publisher AS co_publisher, co.releasedate AS co_releasedate,
					co.review AS co_review, co.cover AS co_cover, cog.title AS co_genre
				FROM releases r
				INNER JOIN category c ON c.id = r.categoryid
				INNER JOIN category cp ON cp.id = c.parentid
				INNER JOIN groups g ON g.id = r.groupid
				LEFT OUTER JOIN movieinfo m ON m.imdbid = r.imdbid AND m.title != ''
				LEFT OUTER JOIN musicinfo mu ON mu.id = r.musicinfoid
				LEFT OUTER JOIN genres mug ON mug.id = mu.genreid
				LEFT OUTER JOIN consoleinfo co ON co.id = r.consoleinfoid
				LEFT OUTER JOIN genres cog ON cog.id = co.genreid %s
				LEFT OUTER JOIN tv_episodes tve ON tve.id = r.tv_episodes_id
				WHERE r.passwordstatus %s
				AND r.nzbstatus = %d
				%s %s %s %s
				ORDER BY postdate DESC %s",
						$this->releases->getConcatenatedCategoryIDs(),
						$cartSearch,
						$this->releases->showPasswords,
						NZB::NZB_ADDED,
						$catSearch,
						($videosId > 0 ? sprintf(' AND r.videos_id = %d %s ', $videosId, ($catSearch == '' ? $catLimit : '')) : ''),
						($aniDbID > 0 ? sprintf(' AND r.anidbid = %d %s ', $aniDbID, ($catSearch == '' ? $catLimit : '')) : ''),
						($airDate > -1 ? sprintf(' AND tve.firstaired >= DATE_SUB(CURDATE(), INTERVAL %d DAY) ', $airDate) : ''),
						(' LIMIT 0,' . ($offset > 100 ? 100 : $offset))
				), true, NN_CACHE_EXPIRY_MEDIUM
		);
		return $sql;
	}

	/**
	 * Get TV shows for RSS.
	 *
	 * @param int   $limit
	 * @param int   $userID
	 * @param array $excludedCats
	 * @param int   $airDate
	 *
	 * @return array
	 */
	public function getShowsRss($limit, $userID = 0, $excludedCats = [], $airDate = -1)
	{
		return $this->pdo->query(
				sprintf("
				SELECT r.*, v.id, v.title, g.name AS group_name,
					CONCAT(cp.title, '-', c.title) AS category_name,
					%s AS category_ids,
					COALESCE(cp.id,0) AS parentCategoryid
				FROM releases r
				INNER JOIN category c ON c.id = r.categoryid
				INNER JOIN category cp ON cp.id = c.parentid
				INNER JOIN groups g ON g.id = r.groupid
				LEFT OUTER JOIN videos v ON v.id = r.videos_id
				LEFT OUTER JOIN tv_episodes tve ON tve.id = r.tv_episodes_id
				WHERE %s %s %s
				AND r.nzbstatus = %d
				AND r.categoryid BETWEEN 5000 AND 5999
				AND r.passwordstatus %s
				ORDER BY postdate DESC %s",
						$this->releases->getConcatenatedCategoryIDs(),
						$this->releases->uSQL($this->pdo->query(sprintf('SELECT videos_id, categoryid FROM userseries WHERE userid = %d', $userID), true), 'videos_id'),
						(count($excludedCats) ? ' AND r.categoryid NOT IN (' . implode(',', $excludedCats) . ')' : ''),
						($airDate > -1 ? sprintf(' AND tve.firstaired >= DATE_SUB(CURDATE(), INTERVAL %d DAY) ', $airDate) : ''),
						NZB::NZB_ADDED,
						$this->releases->showPasswords,
						(' LIMIT ' . ($limit > 100 ? 100 : $limit) . ' OFFSET 0')
				), true, NN_CACHE_EXPIRY_MEDIUM
		);
	}

	/**
	 * Get movies for RSS.
	 *
	 * @param int   $limit
	 * @param int   $userID
	 * @param array $excludedCats
	 *
	 * @return array
	 */
	public function getMyMoviesRss($limit, $userID = 0, $excludedCats = [])
	{
		return $this->pdo->query(
				sprintf("
				SELECT r.*, mi.title AS releasetitle, g.name AS group_name,
					CONCAT(cp.title, '-', c.title) AS category_name,
					%s AS category_ids,
					COALESCE(cp.id,0) AS parentCategoryid
				FROM releases r
				INNER JOIN category c ON c.id = r.categoryid
				INNER JOIN category cp ON cp.id = c.parentid
				INNER JOIN groups g ON g.id = r.groupid
				LEFT OUTER JOIN movieinfo mi ON mi.imdbid = r.imdbid
				WHERE %s %s
				AND r.nzbstatus = %d
				AND r.categoryid BETWEEN 2000 AND 2999
				AND r.passwordstatus %s
				ORDER BY postdate DESC %s",
						$this->releases->getConcatenatedCategoryIDs(),
						$this->releases->uSQL($this->pdo->query(sprintf('SELECT imdbid, categoryid FROM usermovies WHERE userid = %d', $userID), true), 'imdbid'),
						(count($excludedCats) ? ' AND r.categoryid NOT IN (' . implode(',', $excludedCats) . ')' : ''),
						NZB::NZB_ADDED,
						$this->releases->showPasswords,
						(' LIMIT ' . ($limit > 100 ? 100 : $limit) . ' OFFSET 0')
				), true, NN_CACHE_EXPIRY_MEDIUM
		);
	}

	/**
	 * @param $column
	 * @param $table
	 *
	 * @return array|bool
	 */
	public function getFirstInstance($column, $table)
	{
		return $this->pdo->queryOneRow(
				sprintf("
						SELECT %1\$s
						FROM %2\$s
						ORDER BY %1\$s ASC",
						$column,
						$table
				)
		);
	}
}
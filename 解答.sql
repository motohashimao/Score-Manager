-- Q1.テーブル作成(テストテーブル)
-- ・以下のカラムを持つ「tests」テーブルを作成しましょう。

-- カラム名	コメント	データ型	デフォルト値	キー	その他
-- id 	        ID	      int		 主キー	AUTO_INCREMENT
-- test_cd 	1:中間テスト、2:期末テスト、3:総合テスト	tinyint	1
-- test_date 	テスト実施日	date

-- 解答SQL
CREATE TABLE tests(
  id         INT AUTO_INCREMENT  PRIMARY KEY COMMENT 'ID',
  test_cd    TINYINT DEFAULT 1 COMMENT '1:中間テスト、2:期末テスト、3:総合テスト',
  test_date  DATE COMMENT 'テスト実施日'
);


-- Q2.テーブル作成（生徒テーブル）
-- 以下のカラムを持つ「students」テーブルを作成しましょう。

-- カラム名	コメント	データ型	デフォルト値	キー	その他
-- id 	ID	int		主キー	AUTO_INCREMENT
-- class 	クラス名	varchar(1)
-- class_no 	クラス番号	smallint	0
-- last_name 	名前（性）	varchar(25)
-- first_name 	名前（名）	varchar(25)
-- last_name_kana 	ふりがな（性）	varchar(25)
-- first_name_kana 	ふりがな（名）	varchar(25)
-- gender 	1:男、2:女	tinyint
-- birth_date 	生年月日	date

-- 解答SQL
CREATE TABLE students(
  id              INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID',
  class           VARCHAR(1)	COMMENT 'クラス名',
  class_no 		    SMALLINT  DEFAULT 0 COMMENT 'クラス番号',
  last_name 		  VARCHAR(25) COMMENT '名前（性）',
  first_name 		  VARCHAR(25) COMMENT '名前（名）',
  last_name_kana 	VARCHAR(25) COMMENT 'ふりがな（性）',
  first_name_kana VARCHAR(25) COMMENT 'ふりがな（名）',
  gender 		      TINYINT COMMENT '1:男、2:女',
  birth_date 		  DATE COMMENT '生年月日'
);

-- Q3.テーブル作成（テスト科目テーブル）
-- ・以下のカラムを持つ「subjects」テーブルを作成しましょう。

-- カラム名	コメント	データ型	デフォルト値	キー	その他
-- id  ID	smallint		主キー
-- name	科目名	varchar(10)
-- sort  並び順	smallint

-- 解答SQL
CREATE TABLE subjects(
  id    SMALLINT  PRIMARY KEY  COMMENT 'ID',
  name	VARCHAR(10)  COMMENT '科目名',
  sort  SMALLINT  COMMENT '並び順'
);


-- Q4.テーブル作成（点数テーブル）
-- ・以下のカラムを持つ「scores」テーブルを作成しましょう。

-- カラム名	コメント	データ型	デフォルト値	キー	その他
-- id  ID	int		主キー	AUTO_INCREMENT
-- score	点数	tinyint	0
-- student_id  生徒ID	int		外部キー（students.id）
-- subject_id 科目ID	smallint		外部キー（subjects.id）
-- test_id	テストID	int		外部キー（tests.id）

--FOREIGN KEY(列名) REFERENCES 親テーブル名(親列名)を最後に追加する

-- 解答SQL
CREATE TABLE scores(
 id           INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID',
 score        TINYINT DEFAULT 0 COMMENT '点数',
 student_id   INT	COMMENT '生徒ID',
 subject_id 	SMALLINT COMMENT '科目ID',
 test_id	    INT COMMENT'テストID',
  FOREIGN KEY (student_id) REFERENCES students(id),
  FOREIGN KEY (subject_id) REFERENCES subjects(id),
  FOREIGN KEY (test_id) REFERENCES tests(id)
);




-- Q5.テーブル変更
-- ・作成した全てのテーブルに以下のカラムを追加しましょう。

-- カラム名	コメント	データ型	デフォルト値	その他
-- created_at 作成日時	datetime	挿入日時
-- updated_at 	更新日時	datetime	挿入日時	更新する度に日時を更新
-- NOW()を使いましょう。

-- 解答SQL
ALTER TABLE tests
  ADD created_at  DATETIME DEFAULT NOW() COMMENT '作成日時',
  ADD updated_at  DATETIME DEFAULT NOW() ON UPDATE NOW() COMMENT '更新日時';

ALTER TABLE students
  ADD created_at  DATETIME DEFAULT NOW() COMMENT '作成日時',
  ADD updated_at  DATETIME DEFAULT NOW() ON UPDATE NOW() COMMENT '更新日時';

ALTER TABLE subjects
  ADD created_at  DATETIME DEFAULT NOW() COMMENT '作成日時',
  ADD updated_at  DATETIME DEFAULT NOW() ON UPDATE NOW() COMMENT '更新日時';

ALTER TABLE scores
  ADD created_at  DATETIME DEFAULT NOW() COMMENT '作成日時',
  ADD updated_at  DATETIME DEFAULT NOW() ON UPDATE NOW() COMMENT '更新日時';



-- Q6.テーブルデータ追加(テストテーブル)
-- ・「tests」テーブルを以下の情報を元にデータを追加しましょう。

-- テスト種別	テスト実施日
-- 中間テスト	2022年5月9日
-- 期末テスト	2022年7月5日
-- 総合テスト	2022年10月19日

-- 解答SQL
INSERT INTO tests (test_cd,test_date) VALUES
(1,"2022-5-9"),
(2,"2022-7-5"),
(3,"2022-10-19");


-- Q7.テーブルデータ追加(生徒テーブル)
-- ・「students」テーブルを以下の情報を元にデータを追加しましょう。

-- クラス	クラス番号	性	名	性（かな）	名（かな）	性別	生年月日
-- A	1	山田	太郎	やまだ	たろう	男	2011年5月15日
-- A	2	鈴木	花子	すずき	はなこ	女	2012年7月22日
-- A	3	高橋	一郎	たかはし	いちろう	男	2010年11月30日

-- 解答SQL
INSERT INTO students (class,class_no,last_name,first_name,last_name_kana,first_name_kana,gender,birth_date) VALUES
('A', 1,  '山田', '太郎', 'やまだ',   'たろう',   1, "2011-5-15"),
('A', 2,	'鈴木', '花子',	'すずき',	  'はなこ',	  2,	"2012-7-22"),
('A', 3,  '高橋', '一郎',	'たかはし',	'いちろう',	1, "2010-11-30");



-- Q8.テーブルデータ追加(テスト科目テーブル)
-- ・「subjects」テーブルを以下の情報を元にデータを追加しましょう。

-- ID	科目名	並び順
-- 1	英語	30
-- 2	数学	20
-- 3	国語	10
-- 4	理科	40
-- 5	社会	50

-- 解答SQL
INSERT INTO subjects (id,name,sort) VALUES
(1, '英語', 30),
(2, '数学', 20),
(3, '国語', 10),
(4, '理科', 40),
(5, '社会', 50);


-- Q9.テーブルデータ追加(点数テーブル)
-- ・「scores」テーブルを以下の情報を元にデータを追加しましょう。

-- テスト	      生徒名（ふりがな順）	科目（科目並び順）	点数
-- 2022年中間	  鈴木花子	            国語	98		数学	70		英語	45
-- 2022年中間	  高橋一郎	            国語	74		数学	66		英語	23
-- 2022年中間	  山田太郎	            国語	91		数学	54		英語	39


-- ★test_cd 	1:中間テスト、2:期末テスト、3:総合テスト

-- ★クラス	クラス番号	性	名	性（かな）	名（かな）	性別	生年月日
--    A	1	山田	太郎	やまだ	たろう	男	2011年5月15日
--    A	2	鈴木	花子	すずき	はなこ	女	2012年7月22日
--    A	3	高橋	一郎	たかはし	いちろう	男	2010年11月30日

--★1-英語  ２-数学 ３-国語  ４-理科  ５-社会



-- 解答SQL
INSERT INTO scores (score, student_id, subject_id, test_id) VALUES
--(点数 クラス番号  教科番号 テスト種類)
(98,2,3,1),--鈴木花子	  国語	98
(70,2,2,1),--鈴木花子   数学	70
(45,2,1,1);--鈴木花子   英語	45

INSERT INTO scores (score, student_id, subject_id, test_id) VALUES
--(点数 クラス番号  教科番号 テスト種類)
(74,3,3,1),--高橋一郎	  国語	74
(66,3,2,1),--高橋一郎   数学	66
(23,3,1,1);--高橋一郎   英語	23

INSERT INTO scores (score, student_id, subject_id, test_id) VALUES
--(点数 クラス番号  教科番号 テスト種類)
(91,1,3,1),--山田太郎	  国語	91
(54,1,2,1),--山田太郎   数学	54
(39,1,1,1);--山田太郎   英語	39


-- Q10.VIEW作成
-- ・これまでのSQLが正しいかを確認するために、以下の結果を返すVIEWを作成してください。
-- VIEW名：vscores

--( 姓   名   科目  点数)
-- 鈴木	花子	国語	98
-- 鈴木	花子	数学	70
-- 鈴木	花子	英語	45
-- 高橋	一郎	国語	74
-- 高橋	一郎	数学	66
-- 高橋	一郎	英語	23
-- 山田	太郎	国語	91
-- 山田	太郎	数学	54
-- 山田	太郎	英語	39

-- 解答SQL
CREATE VIEW vscores AS
SELECT
    students.last_name,
    students.first_name,
    subjects.name,
    scores.score
FROM
    scores
JOIN--結合
    students ON scores.student_id = students.id
JOIN
    subjects ON scores.subject_id = subjects.id
ORDER BY--昇順にする 昇順「ASC」、降順「DESC」、指定を省略「昇順」)
    students.last_name_kana,--苗字
    subjects.sort;



-- Q11.テーブルデータ更新
-- ・以下のようにデータを更新するSQLを作成してください。
-- ただし、各テーブルの更新条件としてidは使用しないこと

-- studentsテーブル
-- 更新前	更新後
-- 山田太郎	渡辺太郎
-- やまだたろう	わたなべたろう

-- subjectsテーブル（社会のsort）
-- 更新前	更新後
-- 50	35

-- testsテーブル（期末テストの日付）
-- 更新前	更新後
-- 2022年7月5日	2022年7月8日

-- scoresテーブル（鈴木花子の国語の点数）
-- 更新前	更新後
-- 98	96

-- 解答SQL
-- studentsテーブル
UPDATE students
SET
    last_name = '渡辺',
    first_name = '太郎',
    last_name_kana = 'わたなべ',
    first_name_kana = 'たろう'
WHERE
        last_name = '山田'
    AND first_name = '太郎'
    AND last_name_kana = 'やまだ'
    AND first_name_kana = 'たろう';


--subjectsテーブル
UPDATE subjects
SET
    sort = 35
WHERE
    name = '社会' AND sort = 50;


--testsテーブル（期末テストの日付）
UPDATE tests
SET
    test_date = "2022-7-8"
WHERE
    test_cd = 2 AND test_date = "2022-7-5";


-- scoresテーブル（鈴木花子の国語の点数）
--鈴木花子:student_id=2  subject_id=3
UPDATE scores
SET
    score = 96
WHERE
    student_id = 2 AND subject_id = 3;


-- Q12.テーブルデータ削除
-- ・以下のようにデータを削除するSQLを作成してください。
-- ただし、各テーブルの削除条件としてidは使用しないこと

-- studentsテーブル
-- ・名前が「う」で終わる生徒

-- subjectsテーブル
-- ・理科と社会

-- testsテーブル
-- ・2022年7月1日以降に行われたテスト

-- 解答SQL

-- studentsテーブル・名前が「う」で終わる生徒
SET FOREIGN_KEY_CHECKS = 0;

DELETE scores FROM scores
JOIN students ON scores.student_id = students.id
WHERE students.first_name_kana LIKE '%う';

DELETE FROM students
WHERE first_name_kana LIKE '%う';


-- subjectsテーブル・理科と社会
DELETE FROM subjects
WHERE name IN ('理科','社会');

-- testsテーブル・2022年7月1日以降に行われたテスト
DELETE FROM tests
WHERE "2022-7-1" <= test_date;





-- Q13.テーブルデータ全削除
-- ・全てのテーブルのデータをTRUNCATE TABLEを使って削除しましょう。

-- 解答SQL
TRUNCATE TABLE scores;
TRUNCATE TABLE students;
TRUNCATE TABLE subjects;
TRUNCATE TABLE tests;



-- Q14.生徒データ表示
-- ・以下のデータを表示するSQLを作成しましょう。

-- 生徒番号	氏名	氏名かな	性別	生年	生月	生日
-- A1	阿部 直樹	あべ なおき	男性	2010	7	7
-- A2	石井 亮	いしい りょう	男性	2011	8	15
-- A3	小野 聡	おの さとし	男性	2013	2	1
-- A4	金子 隆	かねこ たかし	男性	2011	12	20
-- A5	木村 達也	きむら たつや	男性	2011	6	29
-- A6	小林 太一	こばやし たいち	男性	2012	12	14

-- 確認ポイント
-- ・列名も正しく設定しましょう。
-- ・氏名と氏名かなは性と名の間に半角スペースを入れます。
-- ・生徒番号の昇順に並ぶようにしましょう。
--結合CONCAT

-- 解答SQL
SELECT
    CONCAT(students.class, students.class_no) AS 生徒番号,
    CONCAT(students.last_name, ' ', students.first_name) AS 氏名,
    CONCAT(students.last_name_kana, ' ', students.first_name_kana) AS 氏名かな,
  CASE
    WHEN students.gender = 1 THEN '男性'
    WHEN students.gender = 2 THEN '女性'
  END AS 性別,
    YEAR(students.birth_date) AS 生年,
    MONTH(students.birth_date) AS 月日,
    DAY(students.birth_date) AS 生日
FROM
  students
ORDER BY
  students.class ASC,
  CAST(students.class_no AS UNSIGNED) ASC;



-- Q15.生徒マスタVIEW作成
-- ・以下のデータを出力するVIEWを作成しましょう。（一部データのみ表示）
-- VIEW名：vstudents

-- id	class	class_no	student_no	last_name	first_name	full_name	last_name_kana	first_name_kana	full_name_kana	gender	gender_name	birth_date	format_birth_date
-- 1	A	7	A07	佐藤	太郎	佐藤太郎	さとう	たろう	さとうたろう	1	男性	2011-05-15	2011年05月15日
-- 2	A	24	A24	鈴木	花子	鈴木花子	すずき	はなこ	すずきはなこ	2	女性	2012-07-22	2012年07月22日
-- 3	A	8	A08	高橋	一郎	高橋一郎	たかはし	いちろう	たかはしいちろう	1	男性	2010-11-30	2010年11月30日
-- 4	A	9	A09	田中	次郎	田中次郎	たなか	じろう	たなかじろう	1	男性	2013-01-18	2013年01月18日
-- 5	A	18	A18	伊藤	美咲	伊藤美咲	いとう	みさき	いとうみさき	2	女性	2012-04-05	2012年04月05日

-- 確認ポイント
-- ・LENGHT関数をつかいましょう。
-- ・student_no：class_noの数字が1桁の場合は0埋めにしましょう。
-- ・format_birth_date：◯年◯月◯日の書式にしましょう。

-- 解答SQL
CREATE VIEW vstudents AS
SELECT
    id,
    class,
    class_no,
    CONCAT(class,LPAD(class_no,2,'0')) AS student_no,
    last_name,
    first_name,
    CONCAT(last_name,first_name) AS full_name,
    last_name_kana,
    first_name_kana,
    CONCAT(last_name_kana,first_name_kana) AS	full_name_kana,
    gender,
      CASE
        WHEN gender = 1 THEN '男性'
        WHEN gender = 2 THEN '女性'
        END AS gender_name,
    birth_date,
    DATE_FORMAT(birth_date, '%Y年%c月%e日') AS format_birth_date
FROM
    students;





-- Q16.生徒テストデータ抽出
-- ・以下のデータを表示するSQLを作成しましょう。（一部データのみ表示）

-- 生徒番号   	氏名	合計点	平均点	最高点	最低点
-- A01	阿部 直樹	325	65	95	14
-- A02	石井 亮	346	69	97	39
-- A03	小野 聡	267	53	82	32
-- A04	金子 隆	439	88	100	76
-- A05	木村 達也	243	49	90	15

-- 確認ポイント
-- ・生徒別にtest_idが1の成績データの、合計、平均、最高、最低点を抽出します。
-- ・生徒番号の昇順に並べましょう。
-- ・平均点は四捨五入して表示しましょう。
-- ・vstudentsとscoresを使いましょう。

-- 解答SQL
SELECT
  vstudents.student_no AS 生徒番号,
  CONCAT(vstudents.last_name,' ',first_name) AS 氏名,
  SUM(scores.score) AS 合計点,
  ROUND(AVG(scores.score),0) AS 平均点,
  MAX(scores.score) AS 最高点,
  MIN(scores.score) AS 最低点
FROM
  vstudents
JOIN
  scores  ON vstudents.id = scores.student_id
WHERE
  scores.test_id = 1
GROUP BY
  vstudents.student_no, vstudents.last_name,vstudents.first_name
ORDER BY
  vstudents.student_no ASC;



-- Q17.優秀生徒テストデータ抽出
-- ・Q16で抽出した生徒のうち、優秀な成績を収めた生徒を表彰することになりました。
-- 優秀な生徒とは合計点が350点以上かつ赤点（35点）の科目が無い生徒となります。
-- そのデータを抽出するSQLを作成しましょう。（以下、一部データのみ表示）

-- 生徒番号   	氏名	合計点	平均点	最高点	最低点
-- A04	金子隆	439	88	100	76
-- A07	佐藤太郎	373	75	98	45
-- A12	松井和也	381	76	99	61
-- A15	山本健太	432	86	95	73
-- C01	井上翔	469	94	99	78

-- 解答SQL
SELECT
  vstudents.student_no AS 生徒番号,
  CONCAT(vstudents.last_name,' ',first_name) AS 氏名,
  SUM(scores.score) AS 合計点,
  ROUND(AVG(scores.score),0) AS 平均点,
  MAX(scores.score) AS 最高点,
  MIN(scores.score) AS 最低点
FROM
  vstudents
JOIN
  scores  ON vstudents.id = scores.student_id
WHERE
  scores.test_id = 1
GROUP BY --生徒番号、姓、名でグループ化して、集計結果を生徒ごとに
  vstudents.student_no, vstudents.last_name,vstudents.first_name
HAVING
  SUM(scores.score) >= 350 --合計点が350点以上
  AND MIN(scores.score) >=35 --赤点の科目がない（最低点が35点以上）
ORDER BY
  vstudents.student_no ASC;



-- Q18.テストマスタVIEW作成
-- ・以下のデータを出力するVIEWを作成しましょう。（一部データのみ表示）
-- VIEW名：vtests

-- テスト名
-- 中間テスト 2022年05月実施
-- 期末テスト 2022年07月実施
-- 中間テスト 2022年10月実施
-- 期末テスト 2022年12月実施
-- 総合テスト 2023年02月実施

-- 確認ポイント
-- ・test_cdによりテスト名を決定します。（1:中間テスト、2:期末テスト、3:総合テスト）
-- ・実施日はtest_dateの年月となります。

-- 解答SQL
CREATE VIEW vtests AS
SELECT
  id,
  test_cd,
  test_date,
  CASE
    WHEN test_cd = 1 THEN '中間テスト'
    WHEN test_cd = 2 THEN '期末テスト'
    WHEN test_cd = 3 THEN '統合テスト'
  END AS テスト名,
  CONCAT(YEAR(test_date), '年', LPAD(MONTH(test_date), 2, '0'), '月実施') AS test_name
FROM
  tests;



-- Q19.テストデータ集計
-- ・以下のデータを表示するSQLを作成しましょう。（一部データのみ表示）

-- テスト名	科目名	sort	test_date   	受験者数	合計点	平均点	最高点	最低点
-- 中間テスト 2022年05月実施	国語	10	2022-05-09	150	8142	54	100	10
-- 中間テスト 2022年05月実施	数学	20	2022-05-09	150	8352	56	100	10
-- 中間テスト 2022年05月実施	英語	30	2022-05-09	150	8359	56	99	11
-- 中間テスト 2022年05月実施	理科	40	2022-05-09	150	7799	52	100	10
-- 中間テスト 2022年05月実施	社会	50	2022-05-09	150	8206	55	99	11
-- 期末テスト 2022年07月実施	国語	10	2022-07-05	150	8595	57	100	10
-- 期末テスト 2022年07月実施	数学	20	2022-07-05	150	7938	53	100	10

-- 解答SQL
SELECT
    CASE
        WHEN tests.test_cd = 1 THEN '中間テスト'
        WHEN tests.test_cd = 2 THEN '期末テスト'
        WHEN tests.test_cd = 3 THEN '総合テスト'
    END AS テスト名,
    subjects.name AS 科目名,
    subjects.sort,
    tests.test_date,
    COUNT(scores.score) AS 受験者数,
    SUM(scores.score) AS 合計点,
    ROUND(AVG(scores.score), 0) AS 平均点,
    MAX(scores.score) AS 最高点,
    MIN(scores.score) AS 最低点
FROM
    scores
JOIN
    tests ON scores.test_id = tests.id
JOIN
    subjects ON scores.subject_id = subjects.id
GROUP BY
    tests.test_cd, subjects.name, subjects.sort, tests.test_date
ORDER BY
    tests.test_date, subjects.sort;



-- Q20.科目別の点数データ表示
-- ・以下のデータを表示するSQLを作成しましょう。（一部データのみ表示）

-- id	student_id	subject_id	test_id	score	english	math	japanese	science	social
-- 1	1	1	1	98	98	0	0	0	0
-- 2	1	2	1	70	0	70	0	0	0
-- 3	1	3	1	45	0	0	45	0	0
-- 4	1	4	1	96	0	0	0	96	0
-- 5	1	5	1	64	0	0	0	0	64
-- 6	2	1	1	23	23	0	0	0	0
-- 7	2	2	1	93	0	93	0	0	0
-- 8	2	3	1	25	0	0	25	0	0
-- 9	2	4	1	16	0	0	0	16	0
-- 10	2	5	1	90	0	0	0	0	90

-- 確認ポイント
-- ・english：科目が英語なら、その点数、それ以外であれば0とします。
-- ・math：科目が数学なら、その点数、それ以外であれば0とします。
-- ・japanese：科目が国語なら、その点数、それ以外であれば0とします。
-- ・science：科目が理科なら、その点数、それ以外であれば0とします。
-- ・social：科目が英語なら、その点数、それ以外であれば0とします。

-- 解答SQL
SELECT
  id,
  student_id,
	subject_id,
  test_id,
  score,
  	CASE WHEN subject_id = 1 THEN score ELSE 0 END AS english,
    CASE WHEN subject_id = 2 THEN score ELSE 0 END AS math,
    CASE WHEN subject_id = 3 THEN score ELSE 0 END AS japanese,
    CASE WHEN subject_id = 4 THEN score ELSE 0 END AS science,
    CASE WHEN subject_id = 5 THEN score ELSE 0 END AS social
FROM scores;



-- Q21.科目別の点数VIEW作成
-- ・Q20のデータをグループ化して、以下のデータを表示するSQLを作成し、VIEWを作成しましょう。（一部データのみ表示）
-- VIEW名：vscores
-- ※既存のvscoresは削除します

-- student_id	test_id	english	math	japanese	science	social	total	average
-- 1	1	98	70	45	96	64	373	75
-- 2	1	23	93	25	16	90	247	49
-- 3	1	25	28	57	98	37	245	49
-- 4	1	62	11	39	61	89	262	52
-- 5	1	71	78	75	42	64	330	66

-- 確認ポイント
-- ・各科目の点数はグループ集計で求め、totalとaverageはサブクエリを使って求めます。

-- 解答SQL
DROP VIEW IF EXISTS vscores;--削除


CREATE VIEW vscores AS
SELECT
  student_id,
  test_id,
  	SUM(CASE WHEN subject_id = 1 THEN score ELSE 0 END) AS english,
    SUM(CASE WHEN subject_id = 2 THEN score ELSE 0 END) AS math,
    SUM(CASE WHEN subject_id = 3 THEN score ELSE 0 END) AS japanese,
    SUM(CASE WHEN subject_id = 4 THEN score ELSE 0 END) AS science,
    SUM(CASE WHEN subject_id = 5 THEN score ELSE 0 END) AS social,
    SUM(score) AS total,
    ROUND(AVG(score),0) AS average
FROM scores
GROUP BY student_id, test_id;






-- Q22.順位表作成
-- ・RANK関数を使い、以下のデータを表示するSQLを作成しましょう。（一部データのみ表示）

-- 生徒番号	生徒名	合計点	順位
-- C01	井上翔	469	1
-- A04	金子隆	439	2
-- A15	山本健太	432	3
-- E03	井上太一	409	4
-- C08	林太一	384	5

-- 確認ポイント
-- ・test_id=1のテストの順位を表示してください。

-- 解答SQL
SELECT
    students.id AS 生徒番号,
    CONCAT(students.last_name,' ',students.first_name) AS 生徒名,
    vscores.total AS 合計点,
    RANK() OVER (ORDER BY vscores.total DESC) AS 順位
FROM
    vscores
JOIN
    students ON vscores.student_id = students.id
WHERE
    vscores.test_id = 1
ORDER BY
    順位;



-- Q23.クラス別平均作成
-- ・以下のデータを表示するSQLを作成しましょう。（一部データのみ表示）

-- クラス名	テスト名	test_date   	英語平均	数学平均	国語平均	理科平均	社会平均	合計点平均
-- A	中間テスト 2022年05月実施	2022-05-09	56	53	53	65	61	288
-- B	中間テスト 2022年05月実施	2022-05-09	54	48	57	42	51	251
-- C	中間テスト 2022年05月実施	2022-05-09	56	59	57	49	54	275
-- D	中間テスト 2022年05月実施	2022-05-09	52	58	51	49	51	261
-- E	中間テスト 2022年05月実施	2022-05-09	60	61	53	55	57	286
-- A	期末テスト 2022年07月実施	2022-07-05	55	45	63	53	53	270
-- B	期末テスト 2022年07月実施	2022-07-05	60	58	54	56	49	278

-- 確認ポイント
-- ・クラスごと、テストごとの各教科及び合計点の平均を出します。
-- ・テスト実施日、クラス名の昇順に並べます。

-- 解答SQL
SELECT
  students.class AS クラス名,
  CONCAT(vtests.テスト名,' ',vtests.test_name) AS テスト名,
  vtests.test_date,
  ROUND(AVG(vscores.english), 0) AS 英語平均,
  ROUND(AVG(vscores.math), 0) AS 数学平均,
  ROUND(AVG(vscores.japanese), 0) AS 国語平均,
  ROUND(AVG(vscores.science), 0) AS 理科平均,
  ROUND(AVG(vscores.social), 0) AS 社会平均,
  ROUND(AVG(vscores.total), 0) AS 合計点平均
FROM
  vscores
JOIN
  students ON vscores.student_id = students.id
JOIN
  vtests ON vscores.test_id = vtests.id
GROUP BY
  students.class, vtests.テスト名, vtests.test_name, vtests.test_date
ORDER BY
  vtests.test_date, students.class;


-- Q24.一部点数データ削除
-- ・scoresテーブルからstudent_id : 8、15、29かつ test_id：1のデータを削除しましょう。

-- 解答SQL
DELETE FROM scores WHERE student_id IN (8,15,29) AND test_id = 1;



-- Q25.欠席データ表示
-- ・以下のデータを表示するSQLを作成しましょう。（一部データのみ表示）

-- 生徒番号	生徒名	合計点
-- A01	阿部直樹	325
-- A02	石井亮	346
-- A03	小野聡	欠席
-- A04	金子隆	439
-- A05	木村達也	欠席
-- A06	小林太一	欠席
-- A07	佐藤太郎	373

-- 確認ポイント
-- ・test_id=1のテストのデータを表示してください。
-- ・テストデータが無い生徒の場合、合計点に「欠席」と表示します。
-- ・生徒番号の昇順で並べましょう。

-- 解答SQL
SELECT
  vstudents.student_no AS 生徒番号,
  vstudents.full_name AS 生徒名,
  COALESCE(vscores.total, '欠席') AS 合計点
FROM
  vstudents
LEFT JOIN
  vscores ON vstudents.id = vscores.student_id AND vscores.test_id = 1
ORDER BY
  vstudents.student_no;
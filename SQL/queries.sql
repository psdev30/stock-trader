use psj4;

INSERT INTO user (username, pwd, buying_power, portfolio_value) values
('dev', 'dev', 100000, 100000);

select * from user;

insert into watchlist (watchlist_name, fk_user_id) values 
('test1', 1);

select * from watchlist;

insert into stock (stock_name, stock_ticker) values
('Zillow', 'Z'),
('Microsoft', 'MSFT'),
('Netflix', 'NFLX');
DELETE FROM stock WHERE stock_id = 4;
ALTER TABLE stock AUTO_INCREMENT = 3;
SELECT * FROM stock;

select stock_name  from stock where stock_id = 3;

insert into stock_watchlist (fk_stock_id, fk_watchlist_id) values
(3, 1);

insert into stock_watchlist (fk_stock_id, fk_watchlist_id) values (2, 3);

select * from stock_watchlist;

select * from watchlist;

delete from watchlist where watchlist_id = 3;

alter table watchlist auto_increment = 3;

insert into watchlist (watchlist_name, fk_user_id) VALUES ('test2', 1);

select * from stock;

DELETE FROM stock_watchlist WHERE fk_stock_id=2 AND fk_watchlist_id=3;
 SELECT * FROM stock_watchlist WHERE fk_stock_id=4 AND fk_watchlist_id=3;

DELETE FROM stock_watchlist where stock_watchlist_id = 6;
alter table stock_watchlist auto_increment = 5;

DELETE FROM stock_watchlist WHERE fk_watchlist_id=3;

SELECT fk_stock_id FROM stock_watchlist WHERE fk_watchlist_id = 1;

select * from stock_info;

SELECT * FROM stock_info WHERE fk_stock_id=4;
delete from stock_info where stock_info_id = 1;
alter table stock_info auto_increment = 1;

select * from stock_info;

INSERT INTO stock_info (headquarters, website, ceo, industry, ticker_symbol, market_cap, primary_exchange, fk_stock_id) VALUES ('1600 Amphitheatre Pkwy Mountain View, California', 'https://abc.xyz/', 'Sundar Pichai', 'All Other Telecommunications ', 'GOOGL', 1565723626952, 'NASDAQ/NGS (GLOBAL SELECT MARKET)', 4);

INSERT INTO fundamental (pe_ratio, ttm_eps, ttm_dividend_rate, dividend_yield, beta, next_earnings_date) VALUES (38.881611834207, 58.61, 0, 0, 1.1832578132862, 2021-04-27);

INSERT INTO fundamental (pe_ratio, ttm_eps, ttm_dividend_rate, dividend_yield, beta, next_earnings_date, fk_stock_id) VALUES (38.881611834207, 58.61, 0, 0, 1.1832578132862, '2021-04-27', 4);

select * from fundamental;

delete from fundamental where fundamental_id = 1;
alter table fundamental auto_increment = 1;

insert into position (symbol, shares, avg_share_price, fk_user_id, fk_stock_history_id) values ();
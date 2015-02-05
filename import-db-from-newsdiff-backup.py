#!/usr/bin/env python3
#encoding=utf-8
import sys
import json
import mysql.connector

filename = sys.argv[1]
f = open(filename, 'r')

counter = 0
total = 0
title = ""
content = ""
obj = None

cnx = mysql.connector.connect(host='127.0.0.1', user='root', password='', database='newsdiff', charset='utf8')
cursor = cnx.cursor()
is_exists = False
update_count = 0
insert_count = 0

for line in f:
    line = line.strip('\n')
    counter = counter+1

    if total % 1000 == 0 and counter % 3 == 0:
        print("count: %s, insert_count: %s, update_count: %s" % (total, insert_count, update_count))
    if counter == 1:
        obj = None
        obj = json.loads(line)

        sql = 'select id from news where id = %s' % (obj['id'])
        cursor.execute(sql)
        data = cursor.fetchall()
        if len(data) == 0:
            is_exists = False
        else:
            is_exists = True
        continue

    if counter == 2:
        line = line.strip('"')
        title = line
        continue

    if counter == 3:
        total = total + 1

        line = line.strip('"')
        content = line
        counter = 0

        if is_exists:
            sql = "update news set url = %s, normalized_id = %s, normalized_crc32 = %s, source = %s, created_at = %s, last_fetch_at = %s, last_changed_at = %s, error_count = %s where id = %s;"
            data = (obj['url'], obj['normalized_id'], obj['normalized_crc32'], obj['source'], obj['created_at'], obj['last_fetch_at'], obj['last_changed_at'], obj['error_count'], obj['id'])
            cursor.execute(sql, data)

            sql = "update news_info set time = %s, title = %s, body = %s where news_id = %s;"
            data = (obj['version'], title, content, obj['id'])
            cursor.execute(sql, data)
            update_count = update_count + 1
        else:
            sql = "insert into news(id, url, normalized_id, normalized_crc32, source, created_at, last_fetch_at, last_changed_at, error_count) values(%s, %s, %s, %s, %s, %s, %s, %s, %s);"
            data = (obj['id'], obj['url'], obj['normalized_id'], obj['normalized_crc32'], obj['source'], obj['created_at'], obj['last_fetch_at'], obj['last_changed_at'], obj['error_count'])
            cursor.execute(sql, data)

            sql = "insert into news_info(news_id, time, title, body) values(%s, %s, %s, %s);"
            data = (obj['id'], obj['version'], title, content)
            cursor.execute(sql, data)
            insert_count = insert_count + 1
        cnx.commit()

print("Finished: " + str(total))
f.close()
cursor.close()
cnx.close()



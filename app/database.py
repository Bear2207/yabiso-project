import psycopg2
from psycopg2.extras import RealDictCursor
import streamlit as st

def get_connection():
    """Établir une connexion à la base de données PostgreSQL"""
    try:
        conn = psycopg2.connect(
            database="yabiso",
            user="lulu",
            password="23525689",
            host="localhost",
            port="5432"
        )
        return conn
    except Exception as e:
        st.error(f"Erreur de connexion à la base de données: {e}")
        return None

def execute_query(query, params=None, fetch=True):
    """Exécuter une requête SQL et retourner les résultats"""
    conn = get_connection()
    if conn is None:
        return None
        
    try:
        with conn.cursor(cursor_factory=RealDictCursor) as cur:
            cur.execute(query, params)
            if fetch:
                if "SELECT" in query.upper() or "RETURNING" in query.upper():
                    result = cur.fetchall()
                    return result
            conn.commit()
            return True
    except Exception as e:
        st.error(f"Erreur lors de l'exécution de la requête: {e}")
        return None
    finally:
        if conn:
            conn.close()
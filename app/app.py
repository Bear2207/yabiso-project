import streamlit as st
import pandas as pd
import plotly.express as px
from datetime import datetime, timedelta
from database import execute_query

# Configuration de la page
st.set_page_config(
    page_title="Dashboard Admin - Fitness Center",
    page_icon="💪",
    layout="wide"
)

# Fonction pour vérifier les identifiants
def authenticate_user(email, password):
    query = "SELECT * FROM utilisateurs WHERE email = %s AND mot_de_passe = %s AND role = 'admin'"
    result = execute_query(query, (email, password))
    if result and len(result) > 0:
        return result[0]
    return None

# Fonction de connexion
def login():
    st.title("Connexion Administrateur")
    
    with st.form("login_form"):
        email = st.text_input("Email")
        password = st.text_input("Mot de passe", type="password")
        submit = st.form_submit_button("Se connecter")
        
        if submit:
            user = authenticate_user(email, password)
            if user:
                st.session_state["user"] = user
                st.session_state["logged_in"] = True
                st.rerun()
            else:
                st.error("Identifiants incorrects ou vous n'êtes pas administrateur")

# Fonction pour afficher le dashboard
def dashboard():
    st.sidebar.title("Menu Admin")
    menu_options = ["Tableau de bord", "Utilisateurs", "Abonnements", "Paiements", "Séances", "Progrès", "Notifications"]
    choice = st.sidebar.selectbox("Navigation", menu_options)
    
    # Déconnexion
    if st.sidebar.button("Déconnexion"):
        st.session_state.clear()
        st.rerun()
    
    # Tableau de bord
    if choice == "Tableau de bord":
        st.title("Tableau de bord")
        
        # Statistiques générales
        col1, col2, col3, col4 = st.columns(4)
        
        # Nombre total d'utilisateurs
        users_count = execute_query("SELECT COUNT(*) FROM utilisateurs")[0]['count']
        col1.metric("Utilisateurs", users_count)
        
        # Nombre total de coaches
        coaches_count = execute_query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'coach'")[0]['count']
        col2.metric("Coaches", coaches_count)
        
        # Revenus totaux
        revenue = execute_query("SELECT SUM(montant) FROM paiements WHERE statut = 'payé'")[0]['sum'] or 0
        col3.metric("Revenus", f"{revenue:.2f} €")
        
        # Séances à venir
        upcoming_sessions = execute_query("""
            SELECT COUNT(*) FROM seances 
            WHERE date_seance >= NOW() AND statut = 'réservée'
        """)[0]['count']
        col4.metric("Séances à venir", upcoming_sessions)
        
        # Graphique des paiements par mois
        st.subheader("Revenus par mois")
        monthly_revenue = execute_query("""
            SELECT DATE_TRUNC('month', date_paiement) as mois, 
                   SUM(montant) as total 
            FROM paiements 
            WHERE statut = 'payé'
            GROUP BY mois 
            ORDER BY mois
        """)
        
        if monthly_revenue:
            df_revenue = pd.DataFrame(monthly_revenue)
            df_revenue['mois'] = pd.to_datetime(df_revenue['mois']).dt.strftime('%Y-%m')
            fig = px.bar(df_revenue, x='mois', y='total', title="Revenus mensuels")
            st.plotly_chart(fig)
        
        # Derniers paiements
        st.subheader("Derniers paiements")
        recent_payments = execute_query("""
            SELECT p.*, u.nom, u.prenom, a.nom_abonnement 
            FROM paiements p
            JOIN utilisateurs u ON p.utilisateur_id = u.utilisateur_id
            JOIN abonnements a ON p.abonnement_id = a.abonnement_id
            ORDER BY p.date_paiement DESC 
            LIMIT 10
        """)
        
        if recent_payments:
            st.dataframe(pd.DataFrame(recent_payments))
    
    # Gestion des utilisateurs
    elif choice == "Utilisateurs":
        st.title("Gestion des utilisateurs")
        
        # Afficher tous les utilisateurs
        users = execute_query("SELECT * FROM utilisateurs ORDER BY date_creation DESC")
        if users:
            df_users = pd.DataFrame(users)
            st.dataframe(df_users)
        
        # Ajouter un nouvel utilisateur
        st.subheader("Ajouter un utilisateur")
        with st.form("add_user_form"):
            col1, col2 = st.columns(2)
            nom = col1.text_input("Nom")
            prenom = col2.text_input("Prénom")
            email = st.text_input("Email")
            password = st.text_input("Mot de passe", type="password")
            role = st.selectbox("Rôle", ["client", "coach", "admin"])
            
            if st.form_submit_button("Ajouter"):
                if nom and prenom and email and password:
                    query = """
                        INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role)
                        VALUES (%s, %s, %s, %s, %s)
                    """
                    result = execute_query(query, (nom, prenom, email, password, role), fetch=False)
                    if result:
                        st.success("Utilisateur ajouté avec succès!")
                        st.rerun()
                else:
                    st.error("Veuillez remplir tous les champs")
    
    # Gestion des abonnements
    elif choice == "Abonnements":
        st.title("Gestion des abonnements")
        
        # Afficher tous les abonnements
        abonnements = execute_query("SELECT * FROM abonnements ORDER BY prix")
        if abonnements:
            df_abonnements = pd.DataFrame(abonnements)
            st.dataframe(df_abonnements)
        
        # Ajouter un nouvel abonnement
        st.subheader("Ajouter un abonnement")
        with st.form("add_subscription_form"):
            nom = st.text_input("Nom de l'abonnement")
            prix = st.number_input("Prix", min_value=0.0, step=0.01)
            duree = st.number_input("Durée (jours)", min_value=1, step=1)
            description = st.text_area("Description")
            
            if st.form_submit_button("Ajouter"):
                if nom and prix and duree:
                    query = """
                        INSERT INTO abonnements (nom_abonnement, prix, duree, description)
                        VALUES (%s, %s, %s, %s)
                    """
                    result = execute_query(query, (nom, prix, duree, description), fetch=False)
                    if result:
                        st.success("Abonnement ajouté avec succès!")
                        st.rerun()
                else:
                    st.error("Veuillez remplir tous les champs obligatoires")
    
    # Gestion des paiements
    elif choice == "Paiements":
        st.title("Gestion des paiements")
        
        # Filtres
        col1, col2, col3 = st.columns(3)
        with col1:
            statut_filter = st.selectbox("Filtrer par statut", ["Tous", "payé", "en attente", "annulé"])
        with col2:
            start_date = st.date_input("Date de début")
        with col3:
            end_date = st.date_input("Date de fin")
        
        # Construire la requête avec filtres
        query = """
            SELECT p.*, u.nom, u.prenom, a.nom_abonnement 
            FROM paiements p
            JOIN utilisateurs u ON p.utilisateur_id = u.utilisateur_id
            JOIN abonnements a ON p.abonnement_id = a.abonnement_id
            WHERE 1=1
        """
        params = []
        
        if statut_filter != "Tous":
            query += " AND p.statut = %s"
            params.append(statut_filter)
        
        if start_date:
            query += " AND p.date_paiement >= %s"
            params.append(start_date)
        
        if end_date:
            query += " AND p.date_paiement <= %s"
            params.append(end_date)
        
        query += " ORDER BY p.date_paiement DESC"
        
        payments = execute_query(query, params)
        if payments:
            df_payments = pd.DataFrame(payments)
            st.dataframe(df_payments)
            
            # Statistiques des paiements
            total_revenue = df_payments[df_payments['statut'] == 'payé']['montant'].sum()
            st.metric("Revenus totaux (filtre appliqué)", f"{total_revenue:.2f} €")
    
    # Gestion des séances
    elif choice == "Séances":
        st.title("Gestion des séances")
        
        # Filtres
        col1, col2 = st.columns(2)
        with col1:
            statut_filter = st.selectbox("Filtrer par statut", ["Tous", "réservée", "effectuée", "annulée"])
        with col2:
            date_filter = st.date_input("Filtrer par date")
        
        # Construire la requête
        query = """
            SELECT s.*, 
                   c.nom as coach_nom, c.prenom as coach_prenom,
                   u.nom as client_nom, u.prenom as client_prenom
            FROM seances s
            JOIN utilisateurs c ON s.coach_id = c.utilisateur_id
            JOIN utilisateurs u ON s.utilisateur_id = u.utilisateur_id
            WHERE 1=1
        """
        params = []
        
        if statut_filter != "Tous":
            query += " AND s.statut = %s"
            params.append(statut_filter)
        
        if date_filter:
            query += " AND DATE(s.date_seance) = %s"
            params.append(date_filter)
        
        query += " ORDER BY s.date_seance DESC"
        
        sessions = execute_query(query, params)
        if sessions:
            df_sessions = pd.DataFrame(sessions)
            st.dataframe(df_sessions)
    
    # Gestion des progrès
    elif choice == "Progrès":
        st.title("Suivi des progrès des clients")
        
        # Sélectionner un utilisateur
        users = execute_query("SELECT utilisateur_id, nom, prenom FROM utilisateurs WHERE role = 'client'")
        user_options = {f"{u['prenom']} {u['nom']}": u['utilisateur_id'] for u in users}
        selected_user = st.selectbox("Sélectionner un client", list(user_options.keys()))
        
        if selected_user:
            user_id = user_options[selected_user]
            progress_data = execute_query(
                "SELECT * FROM progres WHERE utilisateur_id = %s ORDER BY date_mesure DESC",
                (user_id,)
            )
            
            if progress_data:
                df_progress = pd.DataFrame(progress_data)
                st.dataframe(df_progress)
                
                # Graphique d'évolution du poids
                if len(df_progress) > 1:
                    fig = px.line(df_progress, x='date_mesure', y='poids', 
                                 title="Évolution du poids")
                    st.plotly_chart(fig)
    
    # Gestion des notifications
    elif choice == "Notifications":
        st.title("Gestion des notifications")
        
        # Afficher les notifications
        notifications = execute_query("""
            SELECT n.*, u.nom, u.prenom 
            FROM notifications n
            JOIN utilisateurs u ON n.utilisateur_id = u.utilisateur_id
            ORDER BY n.date_notification DESC
        """)
        
        if notifications:
            df_notifications = pd.DataFrame(notifications)
            st.dataframe(df_notifications)
        
        # Envoyer une notification
        st.subheader("Envoyer une notification")
        with st.form("send_notification_form"):
            users = execute_query("SELECT utilisateur_id, nom, prenom FROM utilisateurs")
            user_options = {f"{u['prenom']} {u['nom']}": u['utilisateur_id'] for u in users}
            selected_user = st.selectbox("Destinataire", list(user_options.keys()))
            message = st.text_area("Message")
            
            if st.form_submit_button("Envoyer"):
                if selected_user and message:
                    user_id = user_options[selected_user]
                    query = """
                        INSERT INTO notifications (utilisateur_id, message)
                        VALUES (%s, %s)
                    """
                    result = execute_query(query, (user_id, message), fetch=False)
                    if result:
                        st.success("Notification envoyée avec succès!")
                        st.rerun()
                else:
                    st.error("Veuillez remplir tous les champs")

# Point d'entrée de l'application
def main():
    if "logged_in" not in st.session_state:
        st.session_state["logged_in"] = False
        
    if not st.session_state["logged_in"]:
        login()
    else:
        dashboard()

if __name__ == "__main__":
    main()
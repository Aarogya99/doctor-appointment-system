import pandas as pd
from sklearn.cluster import KMeans
import os

try:
    print("Current Working Directory:", os.getcwd())  # Debug
    df = pd.read_csv("../admin/visit_data.csv")
    print("Loaded visit_data.csv successfully")
    
    kmeans = KMeans(n_clusters=3)
    df['cluster'] = kmeans.fit_predict(df[['visit_count']])
    
    df.to_csv("../admin/clustered_patients.csv", index=False)
    print("clustered_patients.csv saved successfully")

except Exception as e:
    print("Error:", str(e))

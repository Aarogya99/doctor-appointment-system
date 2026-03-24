import pandas as pd
from statsmodels.tsa.arima.model import ARIMA

# Load monthly appointment data
df = pd.read_csv("../admin/monthly_appointments.csv")
df['month'] = pd.to_datetime(df['month'])
df.set_index('month', inplace=True)

# Fit ARIMA model
model = ARIMA(df['total'], order=(1, 1, 1))
fit = model.fit()

# Forecast next 3 months
forecast = fit.forecast(steps=3)

# Save to CSV safely
forecast_df = pd.DataFrame({'forecast': forecast.values})
forecast_df.to_csv("../admin/forecast.csv", index=False)

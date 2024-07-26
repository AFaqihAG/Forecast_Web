from prophet import Prophet
import pandas as pd
import json
import sys

def forecast_data(data, periods, freq, changepoint, seasonality):
    # Process data into a DataFrame compatible with Prophet
    df = pd.DataFrame(data)
    df.columns = ['ds', 'y']  # Assuming 'ds' is datetime and 'y' is the value

    # Fit Prophet model
    model = Prophet(
        changepoint_prior_scale= changepoint,
        seasonality_prior_scale=seasonality
    )
    model.fit(df)

    # Make future predictions with the specified frequency
    future = model.make_future_dataframe(periods, freq=freq)
    forecast = model.predict(future)
    
    forecast['ds'] = forecast['ds'].dt.strftime('%Y-%m-%d %H:%M')

    return forecast[['ds', 'yhat']].tail(periods).to_json(orient='records')

def get_frequency(date_increment_type):
    # Map date_increment_type to Prophet frequency strings
    freq_mapping = {
        'seconds': 'S',
        'minutes': 'T',
        'hours': 'H',
        'days': 'D',
        'month': 'M'
    }
    return freq_mapping.get(date_increment_type, 'H')  # Default to 'D' for days if type is unknown

if __name__ == '__main__':
    # Load configuration
    with open('../config/config.json', 'r') as f:
        config = json.load(f)
        
    # Retrieve changepoint_prior_scale and seasonality_prior_scale
    changepoint_prior_scale = float(config.get('changepoint_prior_scale', 0.05))
    seasonality_prior_scale = float(config.get('seasonality_prior_scale', 10.0))
    length_prediction = int(config.get('length_prediction', 100))
    date_increment_type = config.get('date_increment_type', 'hours')
    
    # Read input data from PHP via stdin
    input_data = json.loads(sys.stdin.read())

    # Get the appropriate frequency for Prophet
    freq = get_frequency(date_increment_type)

    # Forecast data
    result = forecast_data(input_data, length_prediction, freq, changepoint_prior_scale, seasonality_prior_scale)

    # Output forecast result
    print(result)

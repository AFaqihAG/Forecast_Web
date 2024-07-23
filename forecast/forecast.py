from prophet import Prophet
import pandas as pd
import json
import sys

def forecast_data(data, periods, freq):
    # Process data into a DataFrame compatible with Prophet
    df = pd.DataFrame(data)
    df.columns = ['ds', 'y']  # Assuming 'ds' is datetime and 'y' is the value

    # Fit Prophet model
    model = Prophet()
    model.fit(df)

    # Make future predictions with the specified frequency
    future = model.make_future_dataframe(periods, freq=freq)
    forecast = model.predict(future)
    
    forecast['ds'] = forecast['ds'].dt.strftime('%Y-%m-%d %H:%M')

    return forecast[['ds', 'yhat']].tail(periods).to_json(orient='records')

if __name__ == '__main__':
    # Read input data from PHP via stdin
    input_data = json.loads(sys.stdin.read())

    # Read periods and date_increment_type from command-line arguments
    periods = int(sys.argv[1])
    date_increment_type = sys.argv[2].lower()

    # Map date_increment_type to pandas frequency strings
    freq_map = {
        'seconds': 'S',
        'minutes': 'T',
        'hours': 'H',
        'days': 'D',
        'months': 'M'  # Added for month increment
    }
    
    freq = freq_map.get(date_increment_type, 'T')  # Default to 'T' if not found

    # Forecast data
    result = forecast_data(input_data, periods, freq)

    # Output forecast result
    print(result)

# Comparison: Logged Request vs Correct Format

## Key Differences Found:

### 1. **govt_registration_date format**
- ❌ **Logged**: `"2017-01-09"` (date only)
- ✅ **Correct**: `"2021-04-14T00:00:00Z"` (ISO 8601 with time)

### 2. **business_address.country**
- ❌ **Logged**: `"country": "USA"` (abbreviation)
- ✅ **Correct**: `"country": "United States"` (full name)

### 3. **incorporation_address**
- ❌ **Logged**: **MISSING** - This field is not included at all
- ✅ **Correct**: Has full `incorporation_address` object with same structure as `business_address`

### 4. **regulator_jurisdiction**
- ❌ **Logged**: `"regulator_jurisdiction": "US-NY"` (with state code)
- ✅ **Correct**: `"regulator_jurisdiction": "USA"` (country only)

### 5. **ref_id** (probably fine, but different)
- **Logged**: UUID format `"cfa171b6-1886-4976-bdc1-75ab0c6df4e3"`
- **Correct**: Simple string `"refid"` (UUIDs should work, but format differs)

## Summary of Issues:
1. Date format needs to be ISO 8601 with timezone
2. Country should be full name "United States" not "USA"
3. Missing `incorporation_address` field entirely
4. Regulator jurisdiction format differs

package Util;

import java.time.Year;

public class Helper {
   public static boolean isNullOrEmpty(String str){
       return str == null || str.isEmpty();

    }

    public static boolean isValidYear(int year) {
        int currentYear = Year.now().getValue();
        return year >= 1450 && year <= currentYear;
    }
}

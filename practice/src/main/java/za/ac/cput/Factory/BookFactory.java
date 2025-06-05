package za.ac.cput.Factory;

import Util.Helper;
import za.ac.cput.Domain.Author;
import za.ac.cput.Domain.Book;
import za.ac.cput.Domain.Publisher;

import java.util.List;

public class BookFactory {
    public static Book createBook(Long bookID, String title, List<Author> authors, List<Publisher> publishers, int year, double price) {
        if(Helper.isNullOrEmpty(title) || !Helper.isValidYear(year)) {
            return null;
        }
        return new Book.BookBuilder()
                .setBookID(bookID)
                .setTitle(title)
                .setAuthors(authors)
                .setPublishers(publishers)
                .setYear(year)
                .setPrice(price)
                .build();

    }
}
